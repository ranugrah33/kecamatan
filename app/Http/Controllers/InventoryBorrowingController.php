<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InventoryBorrowingController extends Controller
{
    public function index()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        $riwayat = InventoryBorrowing::with('details.item')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('masyarakat.peminjaman_inventaris.index', compact('items', 'riwayat'));
    }

    public function create()
    {
        $items = InventoryItem::where('is_active', true)
            ->where('available_quantity', '>', 0)
            ->orderBy('name')
            ->get();
        $user = Auth::user();

        return view('masyarakat.peminjaman_inventaris.create', compact('items', 'user'));
    }

    public function store(Request $request)
    {
        // Normalisasi input waktu ke format 24 jam (HH:mm) WIB sebelum validasi
        if ($request->filled('borrowing_time')) {
            $bTime = trim($request->borrowing_time);
            if (preg_match('/(AM|PM)/i', $bTime)) {
                try {
                    $bTime = Carbon::createFromFormat('h:i A', strtoupper($bTime))->format('H:i');
                } catch (\Exception $e) {
                    try {
                        $bTime = Carbon::parse($bTime)->format('H:i');
                    } catch (\Exception $e2) {}
                }
            } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $bTime, $m)) {
                $bTime = sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
            }
            $request->merge(['borrowing_time' => $bTime]);
        }

        if ($request->filled('return_time')) {
            $rTime = trim($request->return_time);
            if (preg_match('/(AM|PM)/i', $rTime)) {
                try {
                    $rTime = Carbon::createFromFormat('h:i A', strtoupper($rTime))->format('H:i');
                } catch (\Exception $e) {
                    try {
                        $rTime = Carbon::parse($rTime)->format('H:i');
                    } catch (\Exception $e2) {}
                }
            } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $rTime, $m)) {
                $rTime = sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
            }
            $request->merge(['return_time' => $rTime]);
        }

        $validator = Validator::make($request->all(), [
            'applicant_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'institution' => 'nullable|string|max:255',
            'purpose' => 'required|string',
            'penanggung_jawab' => 'required|string|max:255',
            'borrow_date' => 'required|date|after_or_equal:today',
            'borrowing_time' => 'required|date_format:H:i',
            'return_date' => 'required|date|after_or_equal:borrow_date',
            'return_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
            'application_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'applicant_name.required' => 'Nama pemohon wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'purpose.required' => 'Keperluan peminjaman wajib diisi.',
            'penanggung_jawab.required' => 'Penanggung jawab wajib diisi.',
            'borrow_date.required' => 'Tanggal peminjaman wajib diisi.',
            'borrow_date.after_or_equal' => 'Tanggal peminjaman tidak boleh tanggal yang sudah lewat.',
            'borrowing_time.required' => 'Jam peminjaman wajib diisi.',
            'borrowing_time.date_format' => 'Format jam peminjaman harus menggunakan sistem 24 jam (HH:mm WIB, contoh: 08:00, 13:30).',
            'return_date.required' => 'Tanggal pengembalian wajib diisi.',
            'return_date.after_or_equal' => 'Tanggal pengembalian tidak boleh lebih awal dari tanggal peminjaman.',
            'return_time.required' => 'Jam pengembalian wajib diisi.',
            'return_time.date_format' => 'Format jam pengembalian harus menggunakan sistem 24 jam (HH:mm WIB, contoh: 15:30, 16:00).',
            'items.required' => 'Pilih minimal satu barang yang ingin dipinjam.',
            'items.min' => 'Pilih minimal satu barang yang ingin dipinjam.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled(['borrow_date', 'borrowing_time', 'return_date', 'return_time'])) {
                try {
                    $start = Carbon::parse($request->borrow_date . ' ' . $request->borrowing_time);
                    $end = Carbon::parse($request->return_date . ' ' . $request->return_time);

                    if ($end->lessThanOrEqualTo($start)) {
                        if ($request->borrow_date === $request->return_date) {
                            $validator->errors()->add('return_time', 'Jam pengembalian harus lebih besar dari jam peminjaman.');
                        } else {
                            $validator->errors()->add('return_date', 'Tanggal dan waktu pengembalian tidak boleh lebih awal dari waktu peminjaman.');
                        }
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('return_time', 'Format tanggal atau waktu tidak valid.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDateTime = Carbon::parse($request->borrow_date . ' ' . $request->borrowing_time);
        $endDateTime = Carbon::parse($request->return_date . ' ' . $request->return_time);

        // Validasi ketersediaan stok fisik & bentrok jadwal waktu
        foreach ($request->items as $itemData) {
            $item = InventoryItem::find($itemData['id']);
            if (!$item || !$item->is_active) {
                return redirect()->back()->withErrors('Barang tidak tersedia atau tidak aktif.')->withInput();
            }

            if ($item->available_quantity < $itemData['quantity']) {
                return redirect()->back()->withErrors("Jumlah barang \"{$item->name}\" yang tersedia saat ini tidak mencukupi. Tersedia: {$item->available_quantity}, diminta: {$itemData['quantity']}.")->withInput();
            }

            // Pengecekan bentrok jadwal rentang tanggal dan jam
            $activeBorrowings = InventoryBorrowing::whereIn('status', [
                InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
                InventoryBorrowing::STATUS_DISETUJUI,
                InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
                'Diajukan', // Kompatibilitas data transaksi lama
                'Diproses', // Kompatibilitas data transaksi lama
            ])
                ->whereHas('details', function ($q) use ($item) {
                    $q->where('inventory_item_id', $item->id);
                })
                ->with('details')
                ->get();

            $bookedQuantity = 0;
            foreach ($activeBorrowings as $ab) {
                $bStartTime = $ab->borrowing_time ? Carbon::parse($ab->borrowing_time)->format('H:i') : '00:00';
                $bEndTime = $ab->return_time ? Carbon::parse($ab->return_time)->format('H:i') : '23:59';
                $bStart = Carbon::parse($ab->borrow_date->format('Y-m-d') . ' ' . $bStartTime);
                $bEnd = Carbon::parse($ab->return_date->format('Y-m-d') . ' ' . $bEndTime);

                // Dua rentang waktu bertabrakan jika: (bStart < requestedEnd) && (bEnd > requestedStart)
                if ($bStart->lessThan($endDateTime) && $bEnd->greaterThan($startDateTime)) {
                    $detail = $ab->details->firstWhere('inventory_item_id', $item->id);
                    if ($detail) {
                        $bookedQuantity += $detail->quantity;
                    }
                }
            }

            if ($bookedQuantity + $itemData['quantity'] > $item->total_quantity) {
                $sisaRentang = max(0, $item->total_quantity - $bookedQuantity);
                return redirect()->back()->withErrors("Jadwal peminjaman barang \"{$item->name}\" bertabrakan dengan peminjaman lain pada rentang waktu yang dipilih. Tersedia pada rentang tersebut: {$sisaRentang} unit, diminta: {$itemData['quantity']} unit.")->withInput();
            }
        }

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('application_file')) {
            $file = $request->file('application_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_inventaris'), $filename);
            $filePath = 'uploads/surat_inventaris/' . $filename;
        }

        // Generate request code INV-YYYYMMDD-XXXX
        $dateStr = date('Ymd');
        $lastBorrowing = InventoryBorrowing::where('request_code', 'like', 'INV-' . $dateStr . '-%')
            ->orderBy('id', 'desc')->first();
        if ($lastBorrowing) {
            $lastNum = intval(substr($lastBorrowing->request_code, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        $requestCode = 'INV-' . $dateStr . '-' . $newNum;

        DB::beginTransaction();
        try {
            $borrowing = InventoryBorrowing::create([
                'user_id' => Auth::id(),
                'request_code' => $requestCode,
                'applicant_name' => $request->applicant_name,
                'phone' => $request->phone,
                'institution' => $request->institution,
                'purpose' => $request->purpose,
                'penanggung_jawab' => $request->penanggung_jawab,
                'borrow_date' => $request->borrow_date,
                'borrowing_time' => $request->borrowing_time,
                'return_date' => $request->return_date,
                'return_time' => $request->return_time,
                'notes' => $request->notes,
                'application_file' => $filePath,
                'status' => InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
            ]);

            foreach ($request->items as $itemData) {
                InventoryBorrowingDetail::create([
                    'borrowing_id' => $borrowing->id,
                    'inventory_item_id' => $itemData['id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('masyarakat.peminjaman_inventaris.sukses', $borrowing->id);
        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Error Inventory Borrowing: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors('Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function sukses($id)
    {
        $borrowing = InventoryBorrowing::with('details.item')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('masyarakat.peminjaman_inventaris.sukses', compact('borrowing'));
    }

    public function show($id)
    {
        $borrowing = InventoryBorrowing::with('details.item')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('masyarakat.peminjaman_inventaris.show', compact('borrowing'));
    }
}
