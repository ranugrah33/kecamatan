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
    <div class="flex-1">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.peminjaman_aula.index') }}" class="p-2 bg-white rounded-lg border text-gray-600 hover:bg-gray-50 transition">
                    <i class="ph ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Detail Pengajuan Aula</h2>
                    <p class="text-sm text-gray-500">{{ $peminjaman->nomor_pengajuan }}</p>
                </div>
            </div>
            <div>
                @if($peminjaman->status == 'Disetujui' || $peminjaman->status == 'Selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">{{ $peminjaman->status }}</span>
                @elseif($peminjaman->status == 'Menunggu Verifikasi' || $peminjaman->status == 'Diproses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ $peminjaman->status }}</span>
                @elseif($peminjaman->status == 'Perlu Perbaikan')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $peminjaman->status }}</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">{{ $peminjaman->status }}</span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Data Pemohon -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Data Pemohon</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block mb-1">Nama Pemohon</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->user->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">NIK</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->user->nik }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">No. HP</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->user->no_hp }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">Instansi/Organisasi</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->user->masyarakat->instansi ?? '-' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 block mb-1">Alamat</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->user->masyarakat->alamat ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Data Kegiatan & Jadwal -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Data Kegiatan & Jadwal</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="col-span-2">
                            <span class="text-gray-500 block mb-1">Nama Kegiatan</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->nama_kegiatan }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">Jenis Kegiatan</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->jenis_kegiatan }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">Jumlah Peserta</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->jumlah_peserta }} Orang</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 block mb-1">Deskripsi</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->deskripsi_kegiatan }}</span>
                        </div>
                        
                        <div class="col-span-2 my-2 border-t border-gray-100"></div>
                        
                        <div>
                            <span class="text-gray-500 block mb-1">Tanggal Kegiatan</span>
                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->translatedFormat('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">Waktu Kegiatan</span>
                            <span class="font-medium text-gray-900">{{ substr($peminjaman->jam_mulai, 0, 5) }} - {{ substr($peminjaman->jam_selesai, 0, 5) }}</span>
                        </div>
                        
                        <div class="col-span-2 my-2 border-t border-gray-100"></div>
                        
                        <div class="col-span-2">
                            <span class="text-gray-500 block mb-2">Fasilitas yang Dibutuhkan</span>
                            <div class="flex flex-wrap gap-2">
                                @if($peminjaman->fasilitas_dibutuhkan && is_array($peminjaman->fasilitas_dibutuhkan))
                                    @foreach($peminjaman->fasilitas_dibutuhkan as $fasilitas)
                                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-100">{{ $fasilitas }}</span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-span-2 mt-2">
                            <span class="text-gray-500 block mb-1">Catatan Tambahan (Dari Pemohon)</span>
                            <span class="font-medium text-gray-900">{{ $peminjaman->catatan_tambahan ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Dokumen Pendukung -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Dokumen Pendukung</h3>
                    </div>
                    <div class="p-6">
                        @if($peminjaman->dokumens->count() > 0)
                            <ul class="divide-y divide-gray-100 border rounded-lg">
                                @foreach($peminjaman->dokumens as $dokumen)
                                <li class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                                            <i class="ph ph-file-pdf text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $dokumen->nama_dokumen }}</p>
                                            <p class="text-xs text-gray-500">Lampiran</p>
                                        </div>
                                    </div>
                                    <a href="{{ asset('storage/' . $dokumen->path_dokumen) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                        <i class="ph ph-download-simple"></i> Lihat / Unduh
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Tidak ada dokumen yang dilampirkan.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Aksi Verifikasi -->
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Tindakan Admin</h3>
                    </div>
                    <form action="{{ route('admin.peminjaman_aula.updateStatus', $peminjaman->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
                            <select name="status" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="Menunggu Verifikasi" {{ $peminjaman->status == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                <option value="Disetujui" {{ $peminjaman->status == 'Disetujui' ? 'selected' : '' }}>Setujui Pengajuan</option>
                                <option value="Ditolak" {{ $peminjaman->status == 'Ditolak' ? 'selected' : '' }}>Tolak Pengajuan</option>
                                <option value="Perlu Perbaikan" {{ $peminjaman->status == 'Perlu Perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Admin (Opsional)</label>
                            <textarea name="catatan_petugas" rows="4" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Tulis catatan atau alasan penolakan/perbaikan di sini...">{{ $peminjaman->catatan_petugas }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Catatan ini akan muncul di riwayat pelapor.</p>
                        </div>
                        
                        <button type="submit" class="w-full py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
