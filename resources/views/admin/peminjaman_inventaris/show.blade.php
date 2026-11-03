@extends('layouts.admin')
@section('title', 'Detail Pengajuan Inventaris')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.peminjaman_inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"><i class="ph ph-arrow-left"></i> Kembali</a>
    <h1 class="text-xl font-bold text-gray-900">Detail Pengajuan: {{ $borrowing->request_code }}</h1>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif
@if(isset($errors) && $errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Borrowing Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Data Pemohon</h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if (in_array($borrowing->status, ['Disetujui','Dikembalikan','Selesai'])) $bc = 'bg-green-100 text-green-800';
                    elseif ($borrowing->status == 'Diproses') $bc = 'bg-blue-100 text-blue-800';
                    elseif ($borrowing->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                    elseif ($borrowing->status == 'Sedang Dipinjam') $bc = 'bg-amber-100 text-amber-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $borrowing->status }}</span>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Nama Pemohon</dt><dd class="text-gray-900 font-medium">{{ $borrowing->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">No. HP</dt><dd class="text-gray-900">{{ $borrowing->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Instansi</dt><dd class="text-gray-900">{{ $borrowing->institution ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Penanggung Jawab</dt><dd class="text-gray-900">{{ $borrowing->penanggung_jawab }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keperluan</dt><dd class="text-gray-900">{{ $borrowing->purpose }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tanggal Peminjaman</dt><dd class="text-gray-900 font-medium">{{ $borrowing->borrow_date->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Jam Peminjaman</dt><dd class="text-gray-900 font-medium">{{ $borrowing->borrowing_time ? \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') . ' WIB' : '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tanggal Pengembalian</dt><dd class="text-gray-900 font-medium">{{ $borrowing->return_date->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Jam Pengembalian</dt><dd class="text-gray-900 font-medium">{{ $borrowing->return_time ? \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB' : '-' }}</dd></div>
                    @if($borrowing->notes)
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Catatan</dt><dd class="text-gray-900">{{ $borrowing->notes }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="text-sm font-bold text-gray-900">Barang yang Dipinjam</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="bg-gray-50"><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Barang</th><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Jumlah</th><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Tersedia</th></tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($borrowing->details as $d)
                        <tr><td class="px-4 py-3 text-sm text-gray-800">{{ $d->item->name }}</td><td class="px-4 py-3 text-sm font-bold text-gray-700">{{ $d->quantity }}</td><td class="px-4 py-3 text-sm text-gray-500">{{ $d->item->available_quantity }} / {{ $d->item->total_quantity }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($borrowing->application_file)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen</h3>
            <a href="{{ asset($borrowing->application_file) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1"><i class="ph ph-file-pdf text-lg"></i> Download Surat Permohonan</a>
        </div>
        @endif
    </div>

    {{-- Status Update & Reminder Monitoring --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Ubah Status</h3>
            <form action="{{ route('admin.peminjaman_inventaris.updateStatus', $borrowing->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                            @foreach(['Diajukan','Diproses','Disetujui','Ditolak','Sedang Dipinjam','Dikembalikan','Selesai'] as $s)
                            <option value="{{ $s }}" {{ $borrowing->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan / Catatan</label>
                        <textarea name="rejection_reason" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Opsional">{{ $borrowing->rejection_reason }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        {{-- Monitoring Email Reminder Pengembalian --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="ph ph-envelope-simple text-blue-600"></i> Status Reminder Email
            </h3>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 block mb-1">Status Pengingat:</span>
                    @if($borrowing->reminder_sent_at)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <i class="ph ph-check-circle"></i> Berhasil dikirim
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <i class="ph ph-clock"></i> Belum dikirim
                        </span>
                    @endif
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Waktu Pengiriman Terakhir:</span>
                    <span class="font-medium text-gray-800">
                        {{ $borrowing->reminder_sent_at ? $borrowing->reminder_sent_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Status Pengembalian:</span>
                    <span class="font-medium {{ in_array($borrowing->status, ['Dikembalikan', 'Selesai']) ? 'text-emerald-700' : 'text-gray-800' }}">
                        {{ in_array($borrowing->status, ['Dikembalikan', 'Selesai']) ? 'Sudah Dikembalikan (' . $borrowing->status . ')' : 'Belum Dikembalikan' }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Email Penerima:</span>
                    <span class="font-medium text-gray-800 break-all">
                        {{ $borrowing->user->email ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
