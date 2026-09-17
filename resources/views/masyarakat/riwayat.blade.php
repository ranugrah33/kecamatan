@extends('layouts.masyarakat')
@section('title', 'Riwayat Pengajuan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Riwayat Pelayanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau status pengajuan layanan Anda di sini.</p>
</div>

{{-- Filter Tabs --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('masyarakat.riwayat', ['filter' => 'semua']) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $filter == 'semua' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
        Semua
    </a>
    <a href="{{ route('masyarakat.riwayat', ['filter' => 'aula']) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $filter == 'aula' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
        <i class="ph ph-building mr-1"></i> Aula
    </a>
    <a href="{{ route('masyarakat.riwayat', ['filter' => 'inventaris']) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $filter == 'inventaris' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
        <i class="ph ph-package mr-1"></i> Inventaris
    </a>
    <a href="{{ route('masyarakat.riwayat', ['filter' => 'sertifikat']) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $filter == 'sertifikat' ? 'bg-purple-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
        <i class="ph ph-certificate mr-1"></i> Sertifikat / Piagam
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengajuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pelayanan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pengajuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diperbarui</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($riwayat as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['nomor'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        @if($item['jenis_key'] == 'aula')
                            <span class="inline-flex items-center gap-1"><i class="ph ph-building text-blue-500"></i> {{ $item['jenis'] }}</span>
                        @elseif($item['jenis_key'] == 'inventaris')
                            <span class="inline-flex items-center gap-1"><i class="ph ph-package text-indigo-500"></i> {{ $item['jenis'] }}</span>
                        @else
                            <span class="inline-flex items-center gap-1"><i class="ph ph-certificate text-purple-500"></i> {{ $item['jenis'] }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['tanggal'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $badgeClass = 'bg-gray-100 text-gray-800';
                            $s = $item['status'];
                            if (in_array($s, ['Diajukan', 'Menunggu Verifikasi'])) $badgeClass = 'bg-gray-100 text-gray-800';
                            elseif (in_array($s, ['Diproses', 'Surat Diproses', 'Menunggu Dokumen'])) $badgeClass = 'bg-blue-100 text-blue-800';
                            elseif (in_array($s, ['Disetujui', 'Surat Tersedia', 'Selesai', 'Dikembalikan'])) $badgeClass = 'bg-green-100 text-green-800';
                            elseif (in_array($s, ['Ditolak', 'Dibatalkan'])) $badgeClass = 'bg-rose-100 text-rose-800';
                            elseif ($s == 'Perlu Perbaikan') $badgeClass = 'bg-yellow-100 text-yellow-800';
                            elseif ($s == 'Sedang Dipinjam') $badgeClass = 'bg-amber-100 text-amber-800';
                        @endphp
                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                            {{ $item['status'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $item['catatan'] }}">{{ $item['catatan'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['updated_at'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3 items-center">
                        <a href="{{ route($item['route'], $item['id']) }}" class="text-blue-600 hover:text-blue-900 flex items-center gap-1">
                            <i class="ph ph-eye"></i> Detail
                        </a>
                        @if($item['jenis_key'] == 'aula' && $item['status'] === 'Surat Tersedia' && !empty($item['file_surat_final']))
                            <a href="{{ asset('storage/' . $item['file_surat_final']) }}" target="_blank" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 text-xs flex items-center gap-1 shadow-sm transition">
                                <i class="ph ph-download-simple"></i> Surat Izin
                            </a>
                        @endif
                        @if($item['jenis_key'] == 'sertifikat' && $item['status'] === 'Selesai' && !empty($item['file_surat_final']))
                            <a href="{{ route('masyarakat.sertifikat.download', $item['id']) }}" class="px-3 py-1 bg-purple-500 text-white rounded-md hover:bg-purple-600 text-xs flex items-center gap-1 shadow-sm transition">
                                <i class="ph ph-download-simple"></i> Sertifikat
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada riwayat pengajuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <span class="text-sm text-gray-700">Menampilkan {{ count($riwayat) }} data</span>
    </div>
</div>
@endsection
