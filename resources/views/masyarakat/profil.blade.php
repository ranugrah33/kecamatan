@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
    <p class="text-sm text-gray-500 mt-1">Lengkapi data diri Anda untuk keperluan pelayanan masyarakat.</p>
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

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <form action="{{ route('masyarakat.profil.update') }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Akun</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIK</label>
                <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor HP/WhatsApp</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Data Kependudukan Tambahan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor KK</label>
                <input type="text" name="no_kk" value="{{ old('no_kk', $profil->no_kk) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profil->tempat_lahir) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $profil->tanggal_lahir) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Pekerjaan</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $profil->pekerjaan) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Perkawinan</label>
                <select name="status_perkawinan" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Pilih Status</option>
                    <option value="Belum Kawin" {{ old('status_perkawinan', $profil->status_perkawinan) == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                    <option value="Kawin" {{ old('status_perkawinan', $profil->status_perkawinan) == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                    <option value="Cerai Hidup" {{ old('status_perkawinan', $profil->status_perkawinan) == 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                    <option value="Cerai Mati" {{ old('status_perkawinan', $profil->status_perkawinan) == 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                </select>
            </div>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Alamat Domisili</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea name="alamat" rows="3" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('alamat', $profil->alamat) }}</textarea>
            </div>
            <div class="flex gap-4">
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">RT</label>
                    <input type="text" name="rt" value="{{ old('rt', $profil->rt) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="001">
                </div>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">RW</label>
                    <input type="text" name="rw" value="{{ old('rw', $profil->rw) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="002">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Desa/Kelurahan</label>
                <input type="text" name="desa" value="{{ old('desa', $profil->desa) }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
