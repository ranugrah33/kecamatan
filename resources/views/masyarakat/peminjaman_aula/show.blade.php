@extends('layouts.masyarakat')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('masyarakat.riwayat') }}" class="p-2 bg-white rounded-lg border text-gray-600 hover:bg-gray-50 transition">
            <i class="ph ph-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Peminjaman Aula</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $peminjaman->nomor_pengajuan }}</p>
        </div>
    </div>
    
    <div>
        @if($peminjaman->status == 'Disetujui' || $peminjaman->status == 'Selesai')
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                <i class="ph ph-check-circle mr-1.5 text-lg"></i> {{ $peminjaman->status }}
            </span>
        @elseif($peminjaman->status == 'Menunggu Verifikasi' || $peminjaman->status == 'Diproses')
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                <i class="ph ph-clock mr-1.5 text-lg"></i> {{ $peminjaman->status }}
            </span>
        @elseif($peminjaman->status == 'Perlu Perbaikan')
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                <i class="ph ph-warning-circle mr-1.5 text-lg"></i> {{ $peminjaman->status }}
            </span>
        @else
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                <i class="ph ph-x-circle mr-1.5 text-lg"></i> {{ $peminjaman->status }}
            </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Timeline Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-6">Status Pengajuan</h3>
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-between">
                    <!-- Step 1: Diajukan -->
                    <div>
                        <div class="h-8 w-8 rounded-full bg-blue-600 text-white flex items-center justify-center ring-4 ring-white shadow">
                            <i class="ph ph-check text-sm font-bold"></i>
                        </div>
                        <div class="mt-3 text-center w-24 -ml-8">
                            <span class="text-xs font-semibold text-gray-900 block">Diajukan</span>
                        </div>
                    </div>
                    
                    <!-- Step 2: Menunggu Verifikasi -->
                    <div>
                        @php
                            $step2Done = in_array($peminjaman->status, ['Diproses', 'Disetujui', 'Selesai', 'Perlu Perbaikan', 'Ditolak']);
                            $step2Current = $peminjaman->status == 'Menunggu Verifikasi';
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $step2Done ? 'bg-blue-600 text-white' : ($step2Current ? 'bg-blue-100 text-blue-600 border-2 border-blue-600' : 'bg-gray-100 border-2 border-gray-300 text-gray-400') }} flex items-center justify-center ring-4 ring-white shadow">
                            @if($step2Done) <i class="ph ph-check text-sm font-bold"></i> @elseif($step2Current) <div class="h-2.5 w-2.5 bg-blue-600 rounded-full"></div> @endif
                        </div>
                        <div class="mt-3 text-center w-24 -ml-8">
                            <span class="text-xs font-semibold {{ $step2Done || $step2Current ? 'text-gray-900' : 'text-gray-500' }} block">Verifikasi</span>
                        </div>
                    </div>

                    <!-- Step 3: Diproses / Masalah -->
                    <div>
                        @php
                            $step3Done = in_array($peminjaman->status, ['Disetujui', 'Selesai']);
                            $step3Current = in_array($peminjaman->status, ['Diproses', 'Perlu Perbaikan', 'Ditolak', 'Dibatalkan']);
                            $step3Color = 'blue';
                            $step3Label = 'Diproses';
                            
                            if ($peminjaman->status == 'Perlu Perbaikan') { $step3Color = 'yellow'; $step3Label = 'Perbaikan'; }
                            elseif ($peminjaman->status == 'Ditolak') { $step3Color = 'red'; $step3Label = 'Ditolak'; }
                            elseif ($peminjaman->status == 'Dibatalkan') { $step3Color = 'gray'; $step3Label = 'Dibatalkan'; }
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $step3Done ? 'bg-blue-600 text-white' : ($step3Current ? 'bg-'.$step3Color.'-100 text-'.$step3Color.'-600 border-2 border-'.$step3Color.'-600' : 'bg-gray-100 border-2 border-gray-300 text-gray-400') }} flex items-center justify-center ring-4 ring-white shadow">
                            @if($step3Done) <i class="ph ph-check text-sm font-bold"></i> @elseif($step3Current) <div class="h-2.5 w-2.5 bg-{{$step3Color}}-600 rounded-full"></div> @endif
                        </div>
                        <div class="mt-3 text-center w-24 -ml-8">
                            <span class="text-xs font-semibold {{ $step3Done || $step3Current ? 'text-gray-900' : 'text-gray-500' }} block">{{ $step3Label }}</span>
                        </div>
                    </div>

                    <!-- Step 4: Disetujui -->
                    <div>
                        @php
                            $step4Done = $peminjaman->status == 'Selesai';
                            $step4Current = $peminjaman->status == 'Disetujui';
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $step4Done ? 'bg-blue-600 text-white' : ($step4Current ? 'bg-green-100 text-green-600 border-2 border-green-600' : 'bg-gray-100 border-2 border-gray-300 text-gray-400') }} flex items-center justify-center ring-4 ring-white shadow">
                            @if($step4Done) <i class="ph ph-check text-sm font-bold"></i> @elseif($step4Current) <div class="h-2.5 w-2.5 bg-green-600 rounded-full"></div> @endif
                        </div>
                        <div class="mt-3 text-center w-24 -ml-8">
                            <span class="text-xs font-semibold {{ $step4Done || $step4Current ? 'text-gray-900' : 'text-gray-500' }} block">Disetujui</span>
                        </div>
                    </div>

                    <!-- Step 5: Selesai -->
                    <div>
                        @php
                            $step5Current = $peminjaman->status == 'Selesai';
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $step5Current ? 'bg-green-600 text-white' : 'bg-gray-100 border-2 border-gray-300 text-gray-400' }} flex items-center justify-center ring-4 ring-white shadow">
                            @if($step5Current) <i class="ph ph-check text-sm font-bold"></i> @endif
                        </div>
                        <div class="mt-3 text-center w-24 -ml-8">
                            <span class="text-xs font-semibold {{ $step5Current ? 'text-gray-900' : 'text-gray-500' }} block">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($peminjaman->catatan_petugas)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-yellow-800 flex items-center gap-2 mb-2">
                <i class="ph ph-chat-text text-xl"></i> Catatan dari Petugas Kecamatan
            </h3>
            <p class="text-sm text-yellow-900">{{ $peminjaman->catatan_petugas }}</p>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Informasi Kegiatan & Jadwal</h3>
                <span class="text-xs text-gray-500">Dibuat pada {{ \Carbon\Carbon::parse($peminjaman->created_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Nama Kegiatan</p>
                        <p class="font-medium text-gray-900">{{ $peminjaman->nama_kegiatan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jenis Kegiatan</p>
                        <p class="font-medium text-gray-900">{{ $peminjaman->jenis_kegiatan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jumlah Peserta</p>
                        <p class="font-medium text-gray-900">{{ $peminjaman->jumlah_peserta }} Orang</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Deskripsi Kegiatan</p>
                        <p class="font-medium text-gray-900">{{ $peminjaman->deskripsi_kegiatan }}</p>
                    </div>
                </div>

                <hr class="my-6 border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tanggal Peminjaman</p>
                        <p class="font-medium text-gray-900 flex items-center gap-2">
                            <i class="ph ph-calendar text-blue-600"></i>
                            {{ \Carbon\Carbon::parse($peminjaman->tanggal)->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Waktu</p>
                        <p class="font-medium text-gray-900 flex items-center gap-2">
                            <i class="ph ph-clock text-blue-600"></i>
                            {{ substr($peminjaman->jam_mulai, 0, 5) }} - {{ substr($peminjaman->jam_selesai, 0, 5) }} WIB
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm text-gray-500 mb-2">Fasilitas yang Diajukan</p>
                    <div class="flex flex-wrap gap-2">
                        @if($peminjaman->fasilitas_dibutuhkan && count($peminjaman->fasilitas_dibutuhkan) > 0)
                            @foreach($peminjaman->fasilitas_dibutuhkan as $fasilitas)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-200">
                                    {{ $fasilitas }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-sm text-gray-500">-</span>
                        @endif
                    </div>
                </div>
                
                @if($peminjaman->catatan_tambahan)
                <div class="mt-6">
                    <p class="text-sm text-gray-500 mb-1">Catatan Tambahan (Dari Anda)</p>
                    <p class="font-medium text-gray-900">{{ $peminjaman->catatan_tambahan }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="space-y-6">
        <!-- Messages -->
        @if(session('success'))
            <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Aula Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-900">Tempat</h3>
            </div>
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="ph ph-house-line text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $peminjaman->aula->nama }}</h4>
                        <p class="text-sm text-gray-500 mt-1"><i class="ph ph-map-pin mr-1"></i> {{ $peminjaman->aula->lokasi }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-900">Dokumen Pendukung</h3>
            </div>
            <div class="p-6">
                @if($peminjaman->dokumens->count() > 0)
                    <ul class="space-y-3">
                        @foreach($peminjaman->dokumens as $dok)
                        <li class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <i class="ph ph-file-pdf text-red-500 text-2xl"></i>
                                <span class="text-sm font-medium text-gray-700 truncate w-32" title="{{ $dok->nama_dokumen }}">{{ $dok->nama_dokumen }}</span>
                            </div>
                            <a href="{{ asset('storage/' . $dok->path_dokumen) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium bg-blue-50 px-3 py-1 rounded">
                                Buka
                            </a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500 text-center">Tidak ada dokumen yang dilampirkan.</p>
                @endif
            </div>
        </div>

        @if($peminjaman->status === 'Menunggu Verifikasi')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <h3 class="font-semibold text-gray-900 mb-2">Batalkan Pengajuan</h3>
                <p class="text-sm text-gray-500 mb-4">Anda dapat membatalkan pengajuan ini selama masih dalam status menunggu verifikasi.</p>
                <form action="{{ route('masyarakat.peminjaman_aula.cancel', $peminjaman->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="w-full py-2 bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition border border-red-200 flex items-center justify-center gap-2">
                        <i class="ph ph-x-circle text-lg"></i> Batalkan Pengajuan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
