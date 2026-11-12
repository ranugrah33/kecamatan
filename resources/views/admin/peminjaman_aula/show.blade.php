@extends('layouts.admin')
@section('title', 'Detail Peminjaman Aula')

@section('content')

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
                @if(in_array($peminjaman->status, ['Surat Tersedia', 'Selesai', 'Disetujui']))
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">{{ $peminjaman->status_label }}</span>
                @elseif(in_array($peminjaman->status, ['Menunggu Verifikasi', 'Surat Diproses', 'Diproses']))
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ $peminjaman->status_label }}</span>
                @elseif($peminjaman->status == 'Perlu Perbaikan')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $peminjaman->status_label }}</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">{{ $peminjaman->status_label }}</span>
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
                        @if($peminjaman->file_surat_permohonan)
                            <ul class="divide-y divide-gray-100 border rounded-lg">
                                <li class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                                            <i class="ph ph-file-pdf text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Surat Permohonan Peminjaman Aula</p>
                                            <p class="text-xs text-gray-500">File Unggahan Pemohon</p>
                                        </div>
                                    </div>
                                    <a href="{{ asset($peminjaman->file_surat_permohonan) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                        <i class="ph ph-download-simple"></i> Lihat / Unduh
                                    </a>
                                </li>
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Tidak ada dokumen surat permohonan yang dilampirkan.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Aksi Verifikasi -->
            <div class="space-y-6 sticky top-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Tindakan Admin</h3>
                    </div>
                    @php
                        $selectedStatus = old('status', $peminjaman->status);
                    @endphp
                    <form action="{{ route('admin.peminjaman_aula.updateStatus', $peminjaman->id) }}"
                          method="POST"
                          class="p-6 space-y-4"
                          x-data="sipafStatusReasonForm(@js($selectedStatus), ['Perlu Perbaikan', 'Ditolak'], $el)"
                          @submit="handleSubmit($event)">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
                            <select name="status" x-model="status" @change="onStatusChange()" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="Menunggu Verifikasi" {{ $selectedStatus == 'Menunggu Verifikasi' ? 'selected' : '' }}>1. Menunggu Verifikasi</option>
                                <option value="Perlu Perbaikan" {{ $selectedStatus == 'Perlu Perbaikan' ? 'selected' : '' }}>2. Perlu Perbaikan</option>
                                <option value="Surat Diproses" {{ $selectedStatus == 'Surat Diproses' ? 'selected' : '' }}>3. Surat Diproses (Draft)</option>
                                <option value="Surat Tersedia" {{ $selectedStatus == 'Surat Tersedia' ? 'selected' : '' }}>4. Surat Tersedia (Final)</option>
                                <option value="Selesai" {{ $selectedStatus == 'Selesai' ? 'selected' : '' }}>5. Selesai (Kegiatan Rampung)</option>
                                <option value="Ditolak" {{ $selectedStatus == 'Ditolak' ? 'selected' : '' }}>6. Ditolak</option>
                                @if(!in_array($peminjaman->status, ['Menunggu Verifikasi', 'Perlu Perbaikan', 'Surat Diproses', 'Surat Tersedia', 'Selesai', 'Ditolak']))
                                    <option value="{{ $peminjaman->status }}" {{ $selectedStatus == $peminjaman->status ? 'selected' : '' }}>{{ $peminjaman->status_label }}</option>
                                @endif
                            </select>
                        </div>
                        
                        @if(in_array($peminjaman->status, ['Surat Tersedia', 'Disetujui']))
                            @php
                                $tanggalStr = $peminjaman->tanggal instanceof \Carbon\Carbon ? $peminjaman->tanggal->format('Y-m-d') : substr((string)$peminjaman->tanggal, 0, 10);
                                $waktuSelesaiOtomatis = \Carbon\Carbon::parse($tanggalStr . ' ' . $peminjaman->jam_selesai)->addMinutes(30);
                            @endphp
                            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                                <i class="ph ph-clock mr-1 font-bold"></i> Jadwal Selesai Otomatis: <strong>{{ $waktuSelesaiOtomatis->translatedFormat('d M Y, H:i') }} WIB</strong> (30 menit setelah jam kegiatan berakhir).
                            </div>
                        @endif

                        <div x-cloak x-show="requiresReason"
                             class="p-3 rounded-lg border text-xs leading-relaxed"
                             :class="isRevision ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-red-50 border-red-200 text-red-900'">
                            <div class="flex items-start gap-2">
                                <i class="ph text-base shrink-0 mt-0.5" :class="isRevision ? 'ph-warning-circle text-amber-600' : 'ph-envelope-simple text-red-600'"></i>
                                <span>
                                    <strong x-text="isRevision ? 'Wajib Isi Catatan Perbaikan:' : 'Wajib Isi Alasan Penolakan:'"></strong>
                                    Saat status berubah menjadi <strong x-text="status"></strong>, sistem akan otomatis mengirim email pemberitahuan ke pemohon beserta alasan yang Anda tulis.
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700">
                                    <span x-text="isRevision ? 'Alasan / Catatan Perbaikan' : (isRejected ? 'Alasan Penolakan' : 'Catatan Admin (Opsional)')">Catatan Admin (Opsional)</span>
                                    <span x-cloak x-show="requiresReason" class="text-red-600 font-bold">*</span>
                                </label>
                                <span x-cloak x-show="requiresReason" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Wajib diisi</span>
                            </div>
                            <textarea name="catatan_petugas"
                                      x-ref="reasonTextarea"
                                      x-model="reason"
                                      @input="onReasonInput()"
                                      :required="requiresReason"
                                      rows="4"
                                      class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                      :class="clientError || {{ $errors->has('catatan_petugas') ? 'true' : 'false' }} ? 'border-red-500 ring-2 ring-red-100' : (requiresReason ? (isRevision ? 'border-amber-400 focus:ring-amber-200 focus:border-amber-500' : 'border-red-300 focus:ring-red-200 focus:border-red-500') : 'border-gray-300')"
                                      :placeholder="isRevision ? 'Jelaskan bagian yang perlu diperbaiki oleh pemohon...' : (isRejected ? 'Jelaskan alasan penolakan pengajuan secara jelas...' : 'Tulis catatan atau alasan penolakan/perbaikan di sini...')"
                                      placeholder="Tulis catatan atau alasan penolakan/perbaikan di sini...">{{ old('catatan_petugas', $peminjaman->catatan_petugas) }}</textarea>
                            <p x-cloak x-show="clientError" x-text="clientError" class="text-xs text-red-600 font-medium mt-1"></p>
                            @error('catatan_petugas')
                                <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Catatan ini akan muncul di riwayat pelapor dan dikirim melalui email bila status menjadi Perlu Perbaikan atau Ditolak.</p>
                        </div>
                        
                        <button type="submit"
                                :disabled="isSubmitting"
                                :class="isSubmitting ? 'opacity-75 cursor-not-allowed' : ''"
                                class="w-full py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                        </button>
                    </form>
                </div>

                <!-- Proses Surat Perizinan -->
                @if(in_array($peminjaman->status, ['Surat Diproses', 'Surat Tersedia', 'Selesai', 'Disetujui']))
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Proses Surat Perizinan</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600 mb-2">Download draft surat untuk dicetak dan dimintakan tanda tangan Camat.</p>
                        <a href="{{ route('admin.peminjaman_aula.cetakSurat', $peminjaman->id) }}" target="_blank" class="w-full flex items-center justify-center gap-2 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                            <i class="ph ph-printer text-lg"></i> Download Surat (Draft)
                        </a>

                        <hr class="my-4 border-gray-100">

                        <form action="{{ route('admin.peminjaman_aula.uploadSuratFinal', $peminjaman->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat Resmi (Opsional)</label>
                                <input type="text" name="nomor_surat_izin" value="{{ old('nomor_surat_izin', $peminjaman->nomor_surat_izin) }}" placeholder="Contoh: 400.2/008/Kec-Ckp/2026" class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 bg-gray-50 focus:bg-white">
                                <p class="text-xs text-gray-500 mt-1">Masukkan nomor surat fisik dari buku agenda.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Surat Ditandatangani</label>
                                <input type="file" name="file_surat_final" accept=".pdf,.jpg,.jpeg,.png" required class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 bg-gray-50">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Maks 2MB). Sistem akan otomatis memperbarui status menjadi <strong>Surat Tersedia (Final)</strong> dan mengirim notifikasi email.</p>
                            </div>
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                <i class="ph ph-upload-simple text-lg"></i> Upload Surat Ditandatangani
                            </button>
                        </form>
                        
                        @if($peminjaman->file_surat_final)
                        <div class="mt-4 p-3 bg-green-50 border border-green-100 rounded-lg space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-green-800 font-medium">Surat Final Tersedia</span>
                                <div class="flex items-center gap-3">
                                    <a href="{{ asset('storage/' . $peminjaman->file_surat_final) }}" target="_blank" class="text-sm text-green-700 hover:text-green-900 font-semibold underline">Lihat File</a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.peminjaman_aula.downloadSurat', $peminjaman->id) }}" class="text-sm text-green-700 hover:text-green-900 font-semibold underline">Unduh File</a>
                                </div>
                            </div>
                            @if($peminjaman->surat_notified_at)
                                <div class="text-xs text-green-700 flex items-center gap-1.5 pt-1 border-t border-green-200/60">
                                    <i class="ph ph-envelope-simple-check text-sm"></i>
                                    <span>Notifikasi email telah dikirim ke pemohon ({{ $peminjaman->surat_notified_at->translatedFormat('d M Y, H:i') }} WIB).</span>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
@endsection

@include('admin.partials.status_reason_script')
