<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDocument;
use App\Models\InventoryItem;
use App\Mail\InventoryBorrowingSuratFinalMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminInventoryBorrowingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = InventoryBorrowing::with(['user', 'details.item']);

        if ($status) {
            if ($status === InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN) {
                $query->whereIn('status', [
                    InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
                    'Diajukan',
                    'Diproses',
                ]);
            } elseif ($status === InventoryBorrowing::STATUS_SELESAI) {
                $query->whereIn('status', [
                    InventoryBorrowing::STATUS_SELESAI,
                    'Dikembalikan',
                ]);
            } else {
                $query->where('status', $status);
            }
        }

        $borrowings = $query->orderBy('created_at', 'desc')->get();
        return view('admin.peminjaman_inventaris.index', compact('borrowings'));
    }

    public function show($id)
    {
        $borrowing = InventoryBorrowing::with([
            'user.masyarakat',
            'details.item',
            'documents.uploader',
            'activeFinalDocument.uploader'
        ])->findOrFail($id);
        return view('admin.peminjaman_inventaris.show', compact('borrowing'));
    }

    /**
     * Cetak atau unduh draft surat persetujuan inventaris (PDF)
     */
    public function cetakDraftSurat($id)
    {
        $borrowing = InventoryBorrowing::with(['user.masyarakat', 'details.item', 'activeFinalDocument'])->findOrFail($id);

        if (!in_array($borrowing->status, [
            InventoryBorrowing::STATUS_DISETUJUI,
            InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
            InventoryBorrowing::STATUS_SELESAI,
            'Dikembalikan'
        ])) {
            return redirect()->back()->withErrors('Draft surat persetujuan hanya dapat diunduh untuk pengajuan yang telah disetujui.');
        }

        $pdf = Pdf::loadView('admin.peminjaman_inventaris.surat_draft', compact('borrowing'));
        $safeName = 'Draft_Surat_Persetujuan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $borrowing->request_code) . '.pdf';

        return $pdf->stream($safeName);
    }

    /**
     * Unggah surat persetujuan inventaris final yang sudah ditandatangani
     */
    public function uploadSuratFinal(Request $request, $id)
    {
        $borrowing = InventoryBorrowing::with(['user', 'details.item', 'activeFinalDocument'])->findOrFail($id);

        // Aturan 4: Upload surat final hanya diperbolehkan untuk transaksi dengan status Disetujui
        if ($borrowing->status !== InventoryBorrowing::STATUS_DISETUJUI) {
            return redirect()->back()->withErrors("Upload surat persetujuan final hanya diperbolehkan untuk pengajuan dengan status 'Disetujui'. Status saat ini: '{$borrowing->status_label}'.");
        }

        $request->validate([
            'file_surat_final' => 'required|file|mimes:pdf|max:5120',
            'nomor_surat' => 'nullable|string|max:255',
            'kirim_notifikasi_email' => 'nullable|boolean',
        ], [
            'file_surat_final.required' => 'Berkas surat final wajib diunggah.',
            'file_surat_final.mimes' => 'Format berkas surat final harus berupa dokumen PDF (.pdf).',
            'file_surat_final.max' => 'Ukuran berkas surat final maksimal 5 MB.',
        ]);

        if (!$request->hasFile('file_surat_final')) {
            return redirect()->back()->withErrors('Berkas surat final tidak ditemukan.');
        }

        $file = $request->file('file_surat_final');
        $oldActiveDoc = $borrowing->activeFinalDocument;
        $version = $oldActiveDoc ? ($oldActiveDoc->version + 1) : 1;

        $safeRequestCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $borrowing->request_code);
        $filename = time() . '_v' . $version . '_' . $safeRequestCode . '.pdf';
        $subDirectory = 'private/inventory_documents/final';

        $path = $file->storeAs($subDirectory, $filename, 'local');
        if (!$path) {
            return redirect()->back()->withErrors('Gagal menyimpan berkas ke penyimpanan server.');
        }

        DB::beginTransaction();
        try {
            // Jika ada dokumen aktif sebelumnya, tandai sebagai replaced (arsip) tanpa menghapus berkas fisik lama
            if ($oldActiveDoc) {
                $oldActiveDoc->update([
                    'is_active' => false,
                    'document_status' => InventoryBorrowingDocument::STATUS_REPLACED,
                    'notes' => 'Digantikan oleh surat final versi ' . $version . ' pada ' . now()->translatedFormat('d F Y H:i') . ' WIB',
                ]);
            }

            $document = InventoryBorrowingDocument::create([
                'inventory_borrowing_id' => $borrowing->id,
                'document_type' => InventoryBorrowingDocument::TYPE_FINAL,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'document_status' => InventoryBorrowingDocument::STATUS_FINAL_AVAILABLE,
                'is_active' => true,
                'version' => $version,
                'nomor_surat' => $request->filled('nomor_surat') ? $request->nomor_surat : ($oldActiveDoc?->nomor_surat),
                'uploaded_by' => Auth::id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan data dokumen persetujuan inventaris [{$borrowing->request_code}]: " . $e->getMessage());
            return redirect()->back()->withErrors('Terjadi kesalahan saat menyimpan data dokumen di database.');
        }

        // Penanganan notifikasi email:
        // Kirim jika ini versi 1 (unggah pertama) ATAU jika admin mencentang kirim_notifikasi_email saat penggantian
        $shouldNotify = ($version === 1) || $request->boolean('kirim_notifikasi_email');
        $emailSent = false;

        if ($shouldNotify) {
            $recipientEmail = $borrowing->user->email ?? null;
            if (!empty($recipientEmail) && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($recipientEmail)->send(new InventoryBorrowingSuratFinalMail($borrowing, $document));
                    $document->update(['notified_at' => now()]);
                    $emailSent = true;
                    Log::info("Email surat persetujuan inventaris [{$borrowing->request_code}] versi {$version} berhasil dikirim ke {$recipientEmail}.");
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email surat final inventaris [{$borrowing->request_code}] ke {$recipientEmail}: " . $e->getMessage(), [
                        'borrowing_id' => $borrowing->id,
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::warning("Pengiriman email surat persetujuan inventaris dilewati: email pemohon kosong atau tidak valid pada transaksi {$borrowing->request_code} (ID: {$borrowing->id}, User ID: {$borrowing->user_id}).");
            }
        }

        $message = $version > 1
            ? 'Surat persetujuan final (versi ' . $version . ') berhasil diperbarui.' . ($emailSent ? ' Notifikasi email telah dikirimkan ke pemohon.' : '')
            : 'Surat persetujuan final berhasil diunggah.' . ($emailSent ? ' Notifikasi email telah otomatis dikirimkan ke pemohon.' : ' Pemohon kini dapat mengunduhnya melalui akun SIPAF.');

        return redirect()->route('admin.peminjaman_inventaris.show', $borrowing->id)->with('success', $message);
    }

    /**
     * Unduh surat persetujuan final terotorisasi untuk admin
     */
    public function downloadSuratFinal($id, $documentId = null)
    {
        $borrowing = InventoryBorrowing::findOrFail($id);

        if ($documentId) {
            $document = $borrowing->documents()->where('id', $documentId)->firstOrFail();
        } else {
            $document = $borrowing->activeFinalDocument;
        }

        if (!$document || !Storage::disk('local')->exists($document->file_path)) {
            return redirect()->back()->withErrors('Berkas surat persetujuan fisik tidak ditemukan di penyimpanan server.');
        }

        $ext = pathinfo($document->file_name, PATHINFO_EXTENSION) ?: 'pdf';
        $vSuffix = $document->version > 1 ? '_v' . $document->version : '';
        $safeName = 'Surat_Persetujuan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $borrowing->request_code) . $vSuffix . '.' . $ext;

        return Storage::disk('local')->download($document->file_path, $safeName);
    }

    public function updateStatus(Request $request, $id)
    {
        $allowedStatuses = implode(',', InventoryBorrowing::STATUSES);
        $request->validate([
            'status' => "required|in:{$allowedStatuses}",
            'rejection_reason' => 'nullable|string',
        ]);

        $borrowing = InventoryBorrowing::with('details')->findOrFail($id);
        $oldStatus = $borrowing->status;
        $newStatus = $request->status;

        // Aturan E: Status Ditolak dan Selesai merupakan status akhir dan tidak boleh kembali ke proses peminjaman
        if ($oldStatus !== $newStatus && $borrowing->isFinalStatus()) {
            return redirect()->back()->withErrors("Pengajuan dengan status '{$oldStatus}' merupakan status akhir dan tidak dapat diubah kembali.");
        }

        $holdingStockStatuses = [InventoryBorrowing::STATUS_DISETUJUI, InventoryBorrowing::STATUS_SEDANG_DIPINJAM];
        $wasHolding = in_array($oldStatus, $holdingStockStatuses);
        $isHolding = in_array($newStatus, $holdingStockStatuses);

        DB::beginTransaction();
        try {
            // Ketika status berubah menjadi status yang menahan stok (Disetujui / Sedang Dipinjam)
            // dan sebelumnya belum menahan stok — kurangi stok yang tersedia
            if ($isHolding && !$wasHolding) {
                foreach ($borrowing->details as $detail) {
                    $item = InventoryItem::find($detail->inventory_item_id);
                    if ($item->available_quantity < $detail->quantity) {
                        DB::rollback();
                        return redirect()->back()->withErrors("Stok \"{$item->name}\" tidak mencukupi. Tersedia: {$item->available_quantity}, dibutuhkan: {$detail->quantity}.");
                    }
                    $item->decrement('available_quantity', $detail->quantity);
                }
            }

            // Ketika status berubah dari yang menahan stok ke status yang melepas stok
            // (misal: Dikembalikan, Selesai, Ditolak, Diajukan, Diproses) — pulihkan stok
            if ($wasHolding && !$isHolding) {
                foreach ($borrowing->details as $detail) {
                    $item = InventoryItem::find($detail->inventory_item_id);
                    $newAvailable = min($item->total_quantity, $item->available_quantity + $detail->quantity);
                    $item->update(['available_quantity' => $newAvailable]);
                }
            }

            $borrowing->update([
                'status' => $newStatus,
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Terjadi kesalahan saat memperbarui status.');
        }

        // Kirim email notifikasi jika status benar-benar berubah (di luar transaksi DB)
        if ($oldStatus !== $newStatus) {
            $borrowing->refresh();

            // Sesuai kebijakan baru SIPAF: Email notifikasi tidak lagi dikirim saat admin mengubah status menjadi Disetujui.
            // Email notifikasi utama peminjaman HANYA dikirim ketika admin berhasil mengunggah
            // Surat Persetujuan Final (InventoryBorrowingSuratFinalMail) yang telah ditandatangani Camat/pejabat berwenang.
            if ($newStatus === InventoryBorrowing::STATUS_DITOLAK) {
                $borrowing->sendRejectionNotification();
            }
        }

        return redirect()->route('admin.peminjaman_inventaris.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}
