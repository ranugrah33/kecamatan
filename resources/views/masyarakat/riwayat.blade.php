@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Riwayat Pelayanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau status pengajuan layanan Anda di sini.</p>
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Petugas</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diperbarui</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($riwayat as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['nomor'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item['jenis'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['tanggal'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $badgeClass = 'bg-gray-100 text-gray-800';
                            switch($item['status']) {
                                case 'Diajukan': $badgeClass = 'bg-gray-100 text-gray-800'; break;
                                case 'Menunggu Verifikasi': $badgeClass = 'bg-yellow-100 text-yellow-800'; break;
                                case 'Perlu Perbaikan': $badgeClass = 'bg-red-100 text-red-800'; break;
                                case 'Diverifikasi': $badgeClass = 'bg-indigo-100 text-indigo-800'; break;
                                case 'Diproses': $badgeClass = 'bg-blue-100 text-blue-800'; break;
                                case 'Selesai': $badgeClass = 'bg-green-100 text-green-800'; break;
                                case 'Ditolak': $badgeClass = 'bg-rose-100 text-rose-800'; break;
                            }
                        @endphp
                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                            {{ $item['status'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $item['catatan'] }}">{{ $item['catatan'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['updated_at'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada riwayat pengajuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <span class="text-sm text-gray-700">Menampilkan {{ count($riwayat) }} data</span>
        <!-- Pagination placeholder -->
        <nav class="flex items-center gap-1">
            <button disabled class="p-1 rounded text-gray-400 bg-gray-50 cursor-not-allowed"><i class="ph ph-caret-left"></i></button>
            <button class="px-2.5 py-1 rounded text-sm font-medium bg-blue-50 text-blue-600">1</button>
            <button disabled class="p-1 rounded text-gray-400 bg-gray-50 cursor-not-allowed"><i class="ph ph-caret-right"></i></button>
        </nav>
    </div>
</div>
@endsection
