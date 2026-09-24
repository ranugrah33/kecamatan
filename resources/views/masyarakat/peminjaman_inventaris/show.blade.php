@extends('layouts.masyarakat')
@section('title', 'Detail Peminjaman Inventaris')

@section('content')
<div class="mb-6">
    <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
        <i class="ph ph-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Peminjaman Inventaris</h1>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Informasi Pengajuan</h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if (in_array($borrowing->status, ['Disetujui','Dikembalikan','Selesai'])) $bc = 'bg-green-100 text-green-800';
                    elseif ($borrowing->status == 'Diproses') $bc = 'bg-blue-100 text-blue-800';
                    elseif ($borrowing->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                    elseif ($borrowing->status == 'Sedang Dipinjam') $bc = 'bg-amber-100 text-amber-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $borrowing->status }}</span>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500 font-medium">Kode Pengajuan</dt><dd class="text-gray-900 font-bold mt-0.5">{{ $borrowing->request_code }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Nama Pemohon</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">NIK</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->nik ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">No. HP</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Instansi</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->institution ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Penanggung Jawab</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->penanggung_jawab }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Keperluan</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->purpose }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Tanggal Pinjam</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->borrow_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Tanggal Kembali</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->return_date->format('d M Y') }}</dd></div>
                    @if($borrowing->notes)
                    <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Catatan</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->notes }}</dd></div>
                    @endif
                    @if($borrowing->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-red-500 font-medium">Alasan Penolakan</dt><dd class="text-red-700 mt-0.5">{{ $borrowing->rejection_reason }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Daftar Barang --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-bold text-gray-900">Barang yang Dipinjam</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($borrowing->details as $detail)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 flex items-center gap-2">
                                <i class="ph ph-package text-indigo-400"></i> {{ $detail->item->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-semibold">{{ $detail->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Side Info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Timeline Status</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center"><i class="ph ph-paper-plane-tilt text-indigo-600 text-sm"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Diajukan</p>
                        <p class="text-[11px] text-gray-400">{{ $borrowing->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                @if($borrowing->updated_at != $borrowing->created_at)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"><i class="ph ph-arrows-clockwise text-blue-600 text-sm"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ $borrowing->status }}</p>
                        <p class="text-[11px] text-gray-400">{{ $borrowing->updated_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($borrowing->application_file)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen</h3>
            <a href="{{ asset($borrowing->application_file) }}" target="_blank" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                <i class="ph ph-file-pdf text-lg"></i> Surat Permohonan
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
