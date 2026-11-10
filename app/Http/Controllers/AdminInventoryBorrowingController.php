<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

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
        $borrowing = InventoryBorrowing::with(['user.masyarakat', 'details.item'])->findOrFail($id);
        return view('admin.peminjaman_inventaris.show', compact('borrowing'));
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

            if ($newStatus === InventoryBorrowing::STATUS_DISETUJUI) {
                $borrowing->sendApprovalNotification();
            } elseif ($newStatus === InventoryBorrowing::STATUS_DITOLAK) {
                $borrowing->sendRejectionNotification();
            }
        }

        return redirect()->route('admin.peminjaman_inventaris.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}
