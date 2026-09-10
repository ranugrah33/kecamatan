@extends('layouts.masyarakat')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="p-2 bg-white rounded-lg border text-gray-600 hover:bg-gray-50 transition">
        <i class="ph ph-arrow-left text-xl"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Form Pengajuan Peminjaman Aula</h1>
        <p class="text-sm text-gray-500 mt-1">Lengkapi form di bawah ini untuk mengajukan peminjaman {{ $aula->nama }}.</p>
    </div>
</div>

@if ($errors->any())
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-data="peminjamanForm()" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    
    <!-- Form Utama -->
    <form id="mainForm" action="{{ route('masyarakat.peminjaman_aula.store') }}" method="POST" enctype="multipart/form-data" class="p-6" x-show="!showConfirmation" @submit.prevent="previewData">
        @csrf
        
        <!-- Bagian A: Data Pemohon -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">A</span>
            Data Pemohon
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" x-model="formData.nama" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIK</label>
                <input type="text" name="nik" x-model="formData.nik" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor HP</label>
                <input type="text" name="no_hp" x-model="formData.no_hp" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" x-model="formData.email" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50" readonly>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea name="alamat" x-model="formData.alamat" rows="2" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50" readonly></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Instansi / Organisasi (Opsional)</label>
                <input type="text" name="instansi" x-model="formData.instansi" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Masukkan nama instansi atau organisasi Anda jika ada">
            </div>
        </div>

        <!-- Bagian B: Data Kegiatan -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">B</span>
            Data Kegiatan
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kegiatan" x-model="formData.nama_kegiatan" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <select name="jenis_kegiatan" x-model="formData.jenis_kegiatan" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    <option value="">Pilih Jenis Kegiatan</option>
                    <option value="Rapat">Rapat</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Sosialisasi">Sosialisasi</option>
                    <option value="Pelatihan">Pelatihan</option>
                    <option value="Kegiatan Masyarakat">Kegiatan Masyarakat</option>
                    <option value="Kegiatan Organisasi">Kegiatan Organisasi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div x-show="formData.jenis_kegiatan === 'Lainnya'">
                <label class="block text-sm font-medium text-gray-700">Sebutkan Jenis Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="jenis_kegiatan_lainnya" x-model="formData.jenis_kegiatan_lainnya" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" :required="formData.jenis_kegiatan === 'Lainnya'">
            </div>
            <div :class="{'md:col-span-2': formData.jenis_kegiatan !== 'Lainnya'}">
                <label class="block text-sm font-medium text-gray-700">Jumlah Peserta <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah_peserta" x-model="formData.jumlah_peserta" min="1" max="{{ $aula->kapasitas }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                <p class="text-xs text-gray-500 mt-1">Maksimal: {{ $aula->kapasitas }} orang</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Kegiatan <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_kegiatan" x-model="formData.deskripsi_kegiatan" rows="3" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required></textarea>
            </div>
        </div>

        <!-- Bagian C: Jadwal Peminjaman -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">C</span>
            Jadwal Peminjaman
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" x-model="formData.tanggal" :min="today" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_mulai" x-model="formData.jam_mulai" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                <p class="text-xs text-gray-500 mt-1">Buka: {{ substr($aula->jam_buka, 0, 5) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_selesai" x-model="formData.jam_selesai" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                <p class="text-xs text-gray-500 mt-1">Tutup: {{ substr($aula->jam_tutup, 0, 5) }}</p>
            </div>
        </div>

        <!-- Bagian D: Fasilitas yang Dibutuhkan -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">D</span>
            Fasilitas yang Dibutuhkan
        </h2>
        <div class="mb-8">
            <p class="text-sm text-gray-600 mb-3">Pilih fasilitas aula yang ingin Anda gunakan:</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @if($aula->fasilitas)
                    @foreach($aula->fasilitas as $fasilitas)
                    <label class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 transition">
                        <input type="checkbox" name="fasilitas[]" value="{{ $fasilitas }}" x-model="formData.fasilitas" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ $fasilitas }}</span>
                    </label>
                    @endforeach
                @endif
                <label class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 transition">
                    <input type="checkbox" name="fasilitas[]" value="Lainnya" x-model="formData.fasilitas_lainnya_check" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Lainnya</span>
                </label>
            </div>
            <div x-show="formData.fasilitas_lainnya_check" class="mt-3">
                <input type="text" name="fasilitas_lainnya_text" x-model="formData.fasilitas_lainnya_text" class="w-full px-4 py-2 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Sebutkan fasilitas lainnya yang dibutuhkan (opsional)">
            </div>
        </div>

        <!-- Bagian E: Dokumen Pendukung -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">E</span>
            Dokumen Pendukung
        </h2>
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700">Upload Surat Permohonan / Proposal (Opsional)</label>
            <input type="file" name="dokumen" @change="updateFileName" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg bg-gray-50 focus:outline-none">
            <p class="text-xs text-gray-500 mt-1">Format diperbolehkan: PDF, JPG, PNG. Maksimal 2MB.</p>
        </div>

        <!-- Bagian F: Keterangan -->
        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-sm">F</span>
            Keterangan Tambahan
        </h2>
        <div class="mb-8">
            <textarea name="catatan_tambahan" x-model="formData.catatan_tambahan" rows="3" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Tuliskan informasi tambahan mengenai kegiatan Anda jika diperlukan."></textarea>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
                Lanjutkan
            </button>
        </div>
    </form>

    <!-- Ringkasan & Konfirmasi -->
    <div x-show="showConfirmation" x-cloak class="p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">Ringkasan Pengajuan</h2>
        
        <div class="space-y-6">
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b font-medium text-gray-700">DATA PEMOHON</div>
                <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 block">Nama</span><span x-text="formData.nama" class="font-medium"></span></div>
                    <div><span class="text-gray-500 block">NIK</span><span x-text="formData.nik" class="font-medium"></span></div>
                    <div><span class="text-gray-500 block">Instansi</span><span x-text="formData.instansi || '-'" class="font-medium"></span></div>
                </div>
            </div>

            <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b font-medium text-gray-700">DATA KEGIATAN</div>
                <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2"><span class="text-gray-500 block">Nama Kegiatan</span><span x-text="formData.nama_kegiatan" class="font-medium"></span></div>
                    <div><span class="text-gray-500 block">Jenis Kegiatan</span><span x-text="formData.jenis_kegiatan === 'Lainnya' ? formData.jenis_kegiatan_lainnya : formData.jenis_kegiatan" class="font-medium"></span></div>
                    <div><span class="text-gray-500 block">Jumlah Peserta</span><span x-text="formData.jumlah_peserta + ' Orang'" class="font-medium"></span></div>
                </div>
            </div>

            <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b font-medium text-gray-700">JADWAL & FASILITAS</div>
                <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 block">Tanggal</span><span x-text="formData.tanggal" class="font-medium"></span></div>
                    <div><span class="text-gray-500 block">Waktu</span><span x-text="formData.jam_mulai + ' - ' + formData.jam_selesai" class="font-medium"></span></div>
                    <div class="col-span-2">
                        <span class="text-gray-500 block">Fasilitas</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <template x-for="fasilitas in formData.fasilitas">
                                <span class="bg-gray-100 px-2 py-0.5 rounded text-xs border" x-text="fasilitas"></span>
                            </template>
                            <template x-if="formData.fasilitas_lainnya_check && formData.fasilitas_lainnya_text">
                                <span class="bg-gray-100 px-2 py-0.5 rounded text-xs border" x-text="formData.fasilitas_lainnya_text"></span>
                            </template>
                            <template x-if="formData.fasilitas.length === 0 && !formData.fasilitas_lainnya_check">
                                <span>-</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b font-medium text-gray-700">DOKUMEN PENDUKUNG</div>
                <div class="p-4 text-sm font-medium">
                    <span x-text="fileName ? fileName : 'Tidak ada dokumen yang dilampirkan'"></span>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-lg">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" x-model="isConfirmed" class="mt-1 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <span class="text-sm text-blue-900 font-medium">Saya memastikan data yang saya masukkan sudah benar dan saya bertanggung jawab atas kebenarannya.</span>
            </label>
        </div>

        <div class="flex justify-between items-center mt-6">
            <button type="button" @click="showConfirmation = false" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button type="button" @click="submitForm" :disabled="!isConfirmed" :class="!isConfirmed ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 focus:ring-4 focus:ring-blue-300'" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg transition">
                Ajukan Peminjaman
            </button>
        </div>
    </div>

</div>

<script>
function peminjamanForm() {
    return {
        today: new Date().toISOString().split('T')[0],
        showConfirmation: false,
        isConfirmed: false,
        fileName: '',
        formData: {
            nama: "{{ $user->name }}",
            nik: "{{ $user->nik }}",
            no_hp: "{{ $user->no_hp }}",
            email: "{{ $user->email }}",
            alamat: "{{ $profil->alamat ?? '' }}, RT {{ $profil->rt ?? '-' }}/RW {{ $profil->rw ?? '-' }}, {{ $profil->desa ?? '' }}",
            instansi: "",
            nama_kegiatan: "",
            jenis_kegiatan: "",
            jenis_kegiatan_lainnya: "",
            deskripsi_kegiatan: "",
            jumlah_peserta: "",
            tanggal: "",
            jam_mulai: "",
            jam_selesai: "",
            fasilitas: [],
            fasilitas_lainnya_check: false,
            fasilitas_lainnya_text: "",
            catatan_tambahan: ""
        },
        updateFileName(e) {
            this.fileName = e.target.files.length > 0 ? e.target.files[0].name : '';
        },
        previewData() {
            // Validasi Klien
            if(this.formData.jam_mulai >= this.formData.jam_selesai) {
                alert("Jam selesai harus lebih besar daripada jam mulai.");
                return;
            }
            if(this.formData.jam_mulai < "{{ substr($aula->jam_buka, 0, 5) }}" || this.formData.jam_selesai > "{{ substr($aula->jam_tutup, 0, 5) }}") {
                alert("Jam peminjaman harus berada di dalam jam operasional aula ({{ substr($aula->jam_buka, 0, 5) }} - {{ substr($aula->jam_tutup, 0, 5) }}).");
                return;
            }
            if(parseInt(this.formData.jumlah_peserta) > {{ $aula->kapasitas }}) {
                alert("Jumlah peserta melebihi kapasitas aula ({{ $aula->kapasitas }} orang).");
                return;
            }
            
            this.showConfirmation = true;
            window.scrollTo(0, 0);
        },
        submitForm() {
            if(this.isConfirmed) {
                document.getElementById('mainForm').submit();
            }
        }
    }
}
</script>
@endsection
