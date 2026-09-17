@extends('layouts.masyarakat')
@section('title', 'Sertifikat / Piagam')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Sertifikat / Piagam</h1>
        <p class="text-sm text-gray-500 mt-1">Ajukan pembuatan sertifikat atau piagam untuk kegiatan Anda</p>
    </div>
    <a href="{{ route('masyarakat.sertifikat.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold shadow transition">
        <i class="ph ph-plus-circle text-lg"></i> Buat Pengajuan
    </a>
</div>

{{-- Info Card --}}
<div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl border border-purple-100 p-5 mb-6">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="ph ph-info text-purple-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-purple-900 mb-1">Cara Kerja Layanan Ini</h3>
            <ol class="text-xs text-purple-700 space-y-1 list-decimal list-inside">
                <li>Anda membuat pengajuan sertifikat/piagam dengan mengisi form</li>
                <li>Admin akan menerima dan memeriksa permohonan Anda</li>
                <li>Admin membuat sertifikat/piagam dan mengunggah hasilnya</li>
                <li>Anda mendapatkan notifikasi dan dapat mengunduh file</li>
            </ol>
        </div>
    </div>
</div>

{{-- Riwayat Pengajuan --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <i class="ph ph-clock-counter-clockwise text-purple-500"></i> Riwayat Pengajuan
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $r->request_code }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $r->document_type }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $r->activity_name }}</td>
                    <td class="px-6 py-4">
                        @php
                            $bc = 'bg-gray-100 text-gray-800';
                            if ($r->status == 'Selesai') $bc = 'bg-green-100 text-green-800';
                            elseif (in_array($r->status, ['Diproses','Menunggu Dokumen'])) $bc = 'bg-blue-100 text-blue-800';
                            elseif ($r->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $bc }}">{{ $r->status }}</span>
                    </td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        <a href="{{ route('masyarakat.sertifikat.show', $r->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                            <i class="ph ph-eye"></i> Detail
                        </a>
                        @if($r->status == 'Selesai' && $r->latestFile)
                        <a href="{{ route('masyarakat.sertifikat.download', $r->id) }}" class="px-3 py-1 bg-purple-500 text-white rounded-md hover:bg-purple-600 text-xs flex items-center gap-1 shadow-sm transition">
                            <i class="ph ph-download-simple"></i> Unduh
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada pengajuan sertifikat/piagam.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
