@extends('layouts.admin')
@section('title', 'Peminjaman Aula')

@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 p-6 border-b border-gray-100">
        <div>
            <h2 class="text-base font-bold text-gray-900">Daftar Pengajuan Peminjaman Aula</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola dan verifikasi pengajuan peminjaman aula kecamatan</p>
        </div>
        <form action="{{ route('admin.peminjaman_aula.index') }}" method="GET" class="flex gap-2">
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 bg-white text-gray-600" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Menunggu Verifikasi" {{ request('status') == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                <option value="Perlu Perbaikan" {{ request('status') == 'Perlu Perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/80">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">No. Pengajuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Pemohon</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Kegiatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Jadwal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($peminjamans as $item)
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->nomor_pengajuan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $item->user->name ?? '-' }}<br>
                        <span class="text-xs text-gray-400">{{ $item->user->nik ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $item->nama_kegiatan }}<br>
                        <span class="text-xs text-gray-400">{{ $item->jenis_kegiatan }} ({{ $item->jumlah_peserta }} Org)</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}<br>
                        <span class="text-xs text-gray-400">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($item->status == 'Disetujui' || $item->status == 'Selesai')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-700">{{ $item->status }}</span>
                        @elseif($item->status == 'Menunggu Verifikasi' || $item->status == 'Diproses')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-700">{{ $item->status }}</span>
                        @elseif($item->status == 'Perlu Perbaikan')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-700">{{ $item->status }}</span>
                        @else
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-700">{{ $item->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.peminjaman_aula.show', $item->id) }}"
                           class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-100 transition text-xs font-semibold">
                            Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                        <i class="ph ph-inbox text-4xl block mb-2 text-gray-300"></i>
                        Tidak ada data pengajuan yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

