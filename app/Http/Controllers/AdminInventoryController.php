<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowingDetail;

class AdminInventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('name')->get();

        $totalItems = $items->count();
        $totalAvailable = $items->where('is_active', true)->sum('available_quantity');
        $totalBorrowed = $items->sum(function ($item) {
            return $item->total_quantity - $item->available_quantity;
        });
        $totalDamaged = $items->where('condition', 'Rusak')->count();

        return view('admin.inventaris.index', compact('items', 'totalItems', 'totalAvailable', 'totalBorrowed', 'totalDamaged'));
    }

    public function create()
    {
        return view('admin.inventaris.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'total_quantity' => 'required|integer|min:0',
            'condition' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inventaris'), $filename);
            $imagePath = 'uploads/inventaris/' . $filename;
        }

        InventoryItem::create([
            'name' => $request->name,
            'category' => $request->category,
            'total_quantity' => $request->total_quantity,
            'available_quantity' => $request->total_quantity,
            'condition' => $request->condition,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        return redirect()->route('admin.inventaris.index')->with('success', 'Barang inventaris berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);
        return view('admin.inventaris.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'total_quantity' => 'required|integer|min:0',
            'condition' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Calculate the difference in total_quantity to adjust available_quantity
        $borrowed = $item->total_quantity - $item->available_quantity;
        $newAvailable = max(0, $request->total_quantity - $borrowed);

        $data = [
            'name' => $request->name,
            'category' => $request->category,
            'total_quantity' => $request->total_quantity,
            'available_quantity' => $newAvailable,
            'condition' => $request->condition,
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inventaris'), $filename);
            $data['image'] = 'uploads/inventaris/' . $filename;
        }

        $item->update($data);

        return redirect()->route('admin.inventaris.index')->with('success', 'Barang inventaris berhasil diperbarui!');
    }

    public function toggleActive($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        $status = $item->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Barang berhasil {$status}!");
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);

        // Check if item has borrowing history
        $hasHistory = InventoryBorrowingDetail::where('inventory_item_id', $id)->exists();
        if ($hasHistory) {
            return redirect()->back()->withErrors('Barang tidak dapat dihapus karena memiliki riwayat peminjaman. Gunakan fitur nonaktifkan.');
        }

        // Delete image if exists
        if ($item->image && file_exists(public_path($item->image))) {
            unlink(public_path($item->image));
        }

        $item->delete();
        return redirect()->route('admin.inventaris.index')->with('success', 'Barang inventaris berhasil dihapus!');
    }
}
