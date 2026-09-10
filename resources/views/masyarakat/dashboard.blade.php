@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Selamat datang kembali, {{ Auth::user()->name }} (NIK: {{ Auth::user()->nik }})</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Pengajuan</p>
            <p class="text-2xl font-bold text-gray-900">{{ $total_pengajuan }}</p>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
            <i class="ph ph-files text-2xl"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Sedang Diproses</p>
            <p class="text-2xl font-bold text-blue-600">{{ $diproses }}</p>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
            <i class="ph ph-spinner-gap text-2xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600">{{ $selesai }}</p>
        </div>
        <div class="p-3 bg-green-50 text-green-600 rounded-lg">
            <i class="ph ph-check-circle text-2xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Perlu Diperbaiki</p>
            <p class="text-2xl font-bold text-red-600">{{ $perlu_diperbaiki }}</p>
        </div>
        <div class="p-3 bg-red-50 text-red-600 rounded-lg">
            <i class="ph ph-warning-circle text-2xl"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Pengajuan Terbaru -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Pengajuan Terbaru</h2>
                <a href="{{ route('masyarakat.riwayat') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat Semua</a>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Layanan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengajuan_terbaru as $p)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $p['jenis'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p['tanggal'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($p['status'] == 'Selesai')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @elseif($p['status'] == 'Diproses')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notifikasi Singkat -->
    <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Notifikasi Terbaru</h2>
                <a href="{{ route('masyarakat.notifikasi') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat Semua</a>
            </div>
            <div class="p-6 space-y-4">
                @foreach($notifikasi as $n)
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 p-1.5 bg-blue-50 text-blue-600 rounded-full">
                        <i class="ph ph-bell text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800">{{ $n['pesan'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $n['waktu'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
