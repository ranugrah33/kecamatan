<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'institution' => 'nullable|string|max:255',
            'purpose' => 'required|string',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:borrow_date',
            'notes' => 'nullable|string',
            'application_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Validate stock availability
        foreach ($request->items as $itemData) {
            $item = InventoryItem::find($itemData['id']);
            if (!$item || !$item->is_active) {
                return redirect()->back()->withErrors('Barang tidak tersedia.')->withInput();
            }
            if ($item->available_quantity < $itemData['quantity']) {
                return redirect()->back()->withErrors("Jumlah barang \"{$item->name}\" yang tersedia tidak mencukupi. Tersedia: {$item->available_quantity}, diminta: {$itemData['quantity']}.")->withInput();
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

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $borrowing = InventoryBorrowing::create([
                'user_id' => Auth::id(),
                'request_code' => $requestCode,
                'applicant_name' => $request->applicant_name,
                'nik' => $user->nik,
                'phone' => $request->phone,
                'institution' => $request->institution,
                'purpose' => $request->purpose,
                'borrow_date' => $request->borrow_date,
                'return_date' => $request->return_date,
                'notes' => $request->notes,
                'application_file' => $filePath,
                'status' => 'Diajukan',
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
            return redirect()->back()->withErrors('Terjadi kesalahan saat menyimpan pengajuan.')->withInput();
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
