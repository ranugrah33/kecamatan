@extends('layouts.app')

@section('content')
<!-- Navbar -->
<nav class="bg-blue-800 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <span class="font-bold text-xl">Dashboard Admin Kecamatan</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Selamat datang, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white rounded-xl shadow-sm border border-gray-100 p-4 mr-6 hidden md:block">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.peminjaman_aula.index') }}" class="block px-4 py-2 bg-blue-50 text-blue-700 font-medium rounded-lg">Peminjaman Aula</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Data Masyarakat</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Pengaturan</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 overflow-hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Daftar Pengajuan Peminjaman Aula</h2>
            
            <form action="{{ route('admin.peminjaman_aula.index') }}" method="GET" class="flex gap-2">
                <select name="status" class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Verifikasi" {{ request('status') == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="Perlu Perbaikan" {{ request('status') == 'Perlu Perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengajuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($peminjamans as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->nomor_pengajuan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->user->name ?? '-' }}<br>
                            <span class="text-xs text-gray-400">{{ $item->user->nik ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $item->nama_kegiatan }}<br>
                            <span class="text-xs text-gray-400">{{ $item->jenis_kegiatan }} ({{ $item->jumlah_peserta }} Org)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}<br>
                            <span class="text-xs">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($item->status == 'Disetujui' || $item->status == 'Selesai')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $item->status }}</span>
                            @elseif($item->status == 'Menunggu Verifikasi' || $item->status == 'Diproses')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->status }}</span>
                            @elseif($item->status == 'Perlu Perbaikan')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $item->status }}</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.peminjaman_aula.show', $item->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md border border-blue-100 transition">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                            Tidak ada data pengajuan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
