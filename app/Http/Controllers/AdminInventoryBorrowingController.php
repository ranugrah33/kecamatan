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
            $query->where('status', $status);
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
        $request->validate([
            'status' => 'required|in:Diajukan,Diproses,Disetujui,Ditolak,Sedang Dipinjam,Dikembalikan,Selesai',
            'rejection_reason' => 'nullable|string',
        ]);

        $borrowing = InventoryBorrowing::with('details')->findOrFail($id);
        $oldStatus = $borrowing->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            // When status changes to "Disetujui" — reduce available stock
            if ($newStatus === 'Disetujui' && !in_array($oldStatus, ['Disetujui', 'Sedang Dipinjam'])) {
                foreach ($borrowing->details as $detail) {
                    $item = InventoryItem::find($detail->inventory_item_id);
                    if ($item->available_quantity < $detail->quantity) {
                        DB::rollback();
                        return redirect()->back()->withErrors("Stok \"{$item->name}\" tidak mencukupi. Tersedia: {$item->available_quantity}, dibutuhkan: {$detail->quantity}.");
                    }
                    $item->decrement('available_quantity', $detail->quantity);
                }
            }

            // When status changes to "Dikembalikan" or "Selesai" from borrowed state — restore stock
            if (in_array($newStatus, ['Dikembalikan', 'Selesai']) && in_array($oldStatus, ['Disetujui', 'Sedang Dipinjam'])) {
                foreach ($borrowing->details as $detail) {
                    $item = InventoryItem::find($detail->inventory_item_id);
                    $newAvailable = min($item->total_quantity, $item->available_quantity + $detail->quantity);
                    $item->update(['available_quantity' => $newAvailable]);
                }
            }

            // When status changes to "Ditolak" from approved state — restore stock
            if ($newStatus === 'Ditolak' && in_array($oldStatus, ['Disetujui', 'Sedang Dipinjam'])) {
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
            return redirect()->route('admin.peminjaman_inventaris.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Terjadi kesalahan saat memperbarui status.');
        }
    }
}
