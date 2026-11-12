<?php

namespace Tests\Feature;

use App\Mail\PeminjamanAulaSuratFinalMail;
use App\Mail\PengajuanStatusNotificationMail;
use App\Models\Aula;
use App\Models\CertificateRequest;
use App\Models\PeminjamanAula;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PengajuanStatusNotificationMailTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;
    protected User $masyarakatLain;
    protected Aula $aula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.sipaf.test@cikampek.go.id',
            'name' => 'Admin Kecamatan Test',
        ]);

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'pemohon.utama@gmail.com',
            'name' => 'Siti Aminah',
            'no_hp' => '081234567891',
        ]);

        $this->masyarakatLain = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'pemohon.lain@gmail.com',
            'name' => 'Ahmad Fauzi',
            'no_hp' => '081234567892',
        ]);

        $this->aula = Aula::firstOrCreate(
            ['nama' => 'Aula Serbaguna Cikampek'],
            [
                'lokasi' => 'Lantai 2 Kantor Kecamatan',
                'deskripsi' => 'Aula serbaguna kapasitas besar',
                'kapasitas' => 150,
                'fasilitas' => ['AC', 'Sound System', 'Proyektor'],
                'jam_buka' => '08:00:00',
                'jam_tutup' => '17:00:00',
            ]
        );
    }

    protected function createPeminjamanAula(
        string $status = PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI,
        array $overrides = [],
        ?User $owner = null
    ): PeminjamanAula {
        $targetUser = $owner ?? $this->masyarakat;

        return PeminjamanAula::create(array_merge([
            'user_id' => $targetUser->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-MAIL-' . strtoupper(uniqid()),
            'penanggung_jawab' => $targetUser->name,
            'nama_kegiatan' => 'Musyawarah Warga RW 04',
            'jenis_kegiatan' => 'Rapat',
            'deskripsi_kegiatan' => 'Koordinasi program kerja lingkungan tahun 2026',
            'jumlah_peserta' => 45,
            'tanggal' => now()->addDays(10)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '012/RW04/XI/2026',
            'status' => $status,
            'catatan_petugas' => null,
        ], $overrides));
    }

    protected function createCertificateRequest(
        string $status = 'Diajukan',
        array $overrides = [],
        ?User $owner = null
    ): CertificateRequest {
        $targetUser = $owner ?? $this->masyarakat;

        return CertificateRequest::create(array_merge([
            'user_id' => $targetUser->id,
            'request_code' => 'CERT-MAIL-' . strtoupper(uniqid()),
            'applicant_name' => $targetUser->name,
            'certificate_name' => $targetUser->name,
            'phone' => $targetUser->no_hp ?? '081234567891',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Digitalisasi UMKM',
            'activity_date' => now()->addDays(7)->format('Y-m-d'),
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Sertifikat peserta pelatihan UMKM kecamatan',
            'status' => $status,
            'rejection_reason' => null,
        ], $overrides));
    }

    /**
     * Skenario 1: Status berubah menjadi Perlu Perbaikan -> email terkirim ke pemilik pengajuan dengan detail lengkap.
     */
    public function test_1_status_berubah_menjadi_perlu_perbaikan_mengirimkan_email(): void
    {
        Mail::fake();

        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);
        $alasanPerbaikan = "Lampiran surat permohonan belum ditandatangani ketua RW.\nMohon unggah ulang dengan cap resmi.";

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => $alasanPerbaikan,
            ]);

        $response->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
        $response->assertSessionHas('success');

        $fresh = $peminjaman->fresh();
        $this->assertEquals(PeminjamanAula::STATUS_PERLU_PERBAIKAN, $fresh->status);
        $this->assertEquals($alasanPerbaikan, $fresh->catatan_petugas);

        Mail::assertSent(PengajuanStatusNotificationMail::class, 1);
        Mail::assertSent(PengajuanStatusNotificationMail::class, function (PengajuanStatusNotificationMail $mail) use ($peminjaman, $alasanPerbaikan) {
            $hasCorrectRecipient = $mail->hasTo($this->masyarakat->email)
                && !$mail->hasTo($this->admin->email)
                && !$mail->hasTo($this->masyarakatLain->email);

            $subject = $mail->envelope()->subject;
            $rendered = $mail->render();

            return $hasCorrectRecipient
                && str_contains($subject, '[SIPAF] Pengajuan Anda Memerlukan Perbaikan')
                && str_contains($rendered, 'SIPAF — Kecamatan Cikampek')
                && str_contains($rendered, $peminjaman->nomor_pengajuan)
                && str_contains($rendered, 'Peminjaman Aula')
                && str_contains($rendered, 'memerlukan perbaikan')
                && str_contains($rendered, 'Lampiran surat permohonan belum ditandatangani ketua RW.')
                && str_contains($rendered, 'Ajukan kembali permohonan layanan');
        });
    }

    /**
     * Skenario 2A: Status Peminjaman Aula berubah menjadi Ditolak -> email penolakan terkirim.
     */
    public function test_2a_status_aula_berubah_menjadi_ditolak_mengirimkan_email(): void
    {
        Mail::fake();

        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);
        $alasanTolak = 'Aula digunakan untuk agenda rapat koordinasi tingkat kabupaten pada jam tersebut.';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_DITOLAK,
                'catatan_petugas' => $alasanTolak,
            ]);

        $response->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
        $response->assertSessionHas('success');

        $fresh = $peminjaman->fresh();
        $this->assertEquals(PeminjamanAula::STATUS_DITOLAK, $fresh->status);
        $this->assertEquals($alasanTolak, $fresh->catatan_petugas);

        Mail::assertSent(PengajuanStatusNotificationMail::class, 1);
        Mail::assertSent(PengajuanStatusNotificationMail::class, function (PengajuanStatusNotificationMail $mail) use ($peminjaman, $alasanTolak) {
            $subject = $mail->envelope()->subject;
            $rendered = $mail->render();

            return $mail->hasTo($this->masyarakat->email)
                && !$mail->hasTo($this->admin->email)
                && str_contains($subject, '[SIPAF] Pemberitahuan Penolakan Pengajuan')
                && str_contains($rendered, $peminjaman->nomor_pengajuan)
                && str_contains($rendered, 'Peminjaman Aula')
                && str_contains($rendered, 'ditolak')
                && str_contains($rendered, $alasanTolak)
                && str_contains($rendered, 'Informasi Tindak Lanjut');
        });
    }

    /**
     * Skenario 2B: Status Sertifikat / Piagam berubah menjadi Ditolak -> email penolakan terkirim ke pemilik yang benar.
     */
    public function test_2b_status_sertifikat_berubah_menjadi_ditolak_mengirimkan_email(): void
    {
        Mail::fake();

        $certRequest = $this->createCertificateRequest('Diproses', [
            'document_type' => 'Piagam',
        ], $this->masyarakatLain);

        $alasanTolak = 'Surat pengantar resmi dari panitia kegiatan belum dilampirkan.';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Ditolak',
                'rejection_reason' => $alasanTolak,
            ]);

        $response->assertRedirect(route('admin.sertifikat.show', $certRequest->id));
        $response->assertSessionHas('success');

        $fresh = $certRequest->fresh();
        $this->assertEquals('Ditolak', $fresh->status);
        $this->assertEquals($alasanTolak, $fresh->rejection_reason);

        Mail::assertSent(PengajuanStatusNotificationMail::class, 1);
        Mail::assertSent(PengajuanStatusNotificationMail::class, function (PengajuanStatusNotificationMail $mail) use ($certRequest, $alasanTolak) {
            $subject = $mail->envelope()->subject;
            $rendered = $mail->render();

            return $mail->hasTo($this->masyarakatLain->email)
                && !$mail->hasTo($this->masyarakat->email)
                && !$mail->hasTo($this->admin->email)
                && str_contains($subject, '[SIPAF] Pemberitahuan Penolakan Pengajuan')
                && str_contains($rendered, $certRequest->request_code)
                && str_contains($rendered, 'Pengajuan Piagam')
                && str_contains($rendered, $alasanTolak)
                && str_contains($rendered, 'Informasi Tindak Lanjut');
        });
    }

    /**
     * Skenario 3: Status berubah menjadi Disetujui (dan status lain non-target) -> tidak ada email dari fitur ini.
     */
    public function test_3_status_berubah_menjadi_disetujui_atau_status_lain_tidak_mengirim_email_fitur_ini(): void
    {
        Mail::fake();

        $certRequest = $this->createCertificateRequest('Diajukan');

        $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Disetujui',
            ])
            ->assertRedirect(route('admin.sertifikat.show', $certRequest->id));

        $this->assertEquals('Disetujui', $certRequest->fresh()->status);

        $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Sertifikat Tersedia',
            ])
            ->assertRedirect(route('admin.sertifikat.show', $certRequest->id));

        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_SURAT_DIPROSES,
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));

        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_SELESAI,
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));

        Mail::assertNotSent(PengajuanStatusNotificationMail::class);
        Mail::assertNotSent(PeminjamanAulaSuratFinalMail::class);
    }

    /**
     * Skenario 4: Status tidak berubah -> tidak ada email dikirim.
     */
    public function test_4_status_tidak_berubah_tidak_mengirim_email(): void
    {
        Mail::fake();

        $peminjamanRevision = $this->createPeminjamanAula(PeminjamanAula::STATUS_PERLU_PERBAIKAN, [
            'catatan_petugas' => 'Catatan awal perbaikan',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjamanRevision->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => 'Catatan perbaikan diperbarui tanpa ganti status',
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjamanRevision->id));

        $certRejected = $this->createCertificateRequest('Ditolak', [
            'rejection_reason' => 'Alasan awal ditolak',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRejected->id), [
                'status' => 'Ditolak',
                'rejection_reason' => 'Catatan penolakan diperbarui tanpa ganti status',
            ])
            ->assertRedirect(route('admin.sertifikat.show', $certRejected->id));

        Mail::assertNothingSent();
    }

    /**
     * Skenario 5: Alasan kosong saat Perlu Perbaikan atau Ditolak -> validasi gagal, DB tidak berubah, email tidak dikirim.
     */
    public function test_5_alasan_kosong_gagal_validasi_dan_tidak_menyimpan_perubahan(): void
    {
        Mail::fake();

        // 5A. Peminjaman Aula -> Perlu Perbaikan tanpa alasan (kosong & spasi)
        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);

        foreach (['', '   ', null] as $emptyNote) {
            $resp = $this->actingAs($this->admin)
                ->from(route('admin.peminjaman_aula.show', $peminjaman->id))
                ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                    'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                    'catatan_petugas' => $emptyNote,
                ]);

            $resp->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
            $resp->assertSessionHasErrors('catatan_petugas');
            $this->assertEquals(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI, $peminjaman->fresh()->status);
        }

        // 5B. Peminjaman Aula -> Ditolak tanpa alasan
        $respAulaTolak = $this->actingAs($this->admin)
            ->from(route('admin.peminjaman_aula.show', $peminjaman->id))
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_DITOLAK,
                'catatan_petugas' => '',
            ]);

        $respAulaTolak->assertSessionHasErrors('catatan_petugas');
        $this->assertEquals(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI, $peminjaman->fresh()->status);

        // 5C. Sertifikat -> Ditolak tanpa alasan
        $certRequest = $this->createCertificateRequest('Diajukan');
        $respCertTolak = $this->actingAs($this->admin)
            ->from(route('admin.sertifikat.show', $certRequest->id))
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Ditolak',
                'rejection_reason' => '   ',
            ]);

        $respCertTolak->assertSessionHasErrors('rejection_reason');
        $this->assertEquals('Diajukan', $certRequest->fresh()->status);

        // 5D. Untuk status lain (mis. Surat Diproses / Diproses), alasan kosong tetap diizinkan
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_SURAT_DIPROSES,
                'catatan_petugas' => null,
            ])
            ->assertSessionHasNoErrors();
        $this->assertEquals(PeminjamanAula::STATUS_SURAT_DIPROSES, $peminjaman->fresh()->status);

        Mail::assertNothingSent();
    }

    /**
     * Skenario 6: Email masyarakat kosong atau tidak valid -> penanganan aman, data tetap tersimpan, log warning tercatat.
     */
    public function test_6_email_masyarakat_kosong_atau_tidak_valid_ditangani_aman(): void
    {
        Mail::fake();

        $userInvalidEmail = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'email-tidak-valid',
            'name' => 'Pemohon Tanpa Email Valid',
        ]);

        $peminjaman = $this->createPeminjamanAula(
            PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI,
            [],
            $userInvalidEmail
        );

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context = []) use ($peminjaman) {
                return str_contains($message, 'email pemohon kosong atau tidak valid')
                    && ($context['request_code'] ?? null) === $peminjaman->nomor_pengajuan;
            });
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => 'Perbaiki tanggal kegiatan pada surat permohonan.',
            ]);

        $response->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
        $response->assertSessionHas('success');

        $fresh = $peminjaman->fresh();
        $this->assertEquals(PeminjamanAula::STATUS_PERLU_PERBAIKAN, $fresh->status);
        $this->assertEquals('Perbaiki tanggal kegiatan pada surat permohonan.', $fresh->catatan_petugas);

        Mail::assertNothingSent();
    }

    /**
     * Skenario 7: Kegagalan SMTP -> data pengajuan tetap tersimpan di database dan error tercatat di log.
     */
    public function test_7_kegagalan_smtp_tidak_merusak_data_pengajuan_yang_tersimpan(): void
    {
        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);
        $alasan = 'Dokumen pendukung belum lengkap untuk jadwal acara besar.';

        Mail::shouldReceive('to')
            ->once()
            ->with($this->masyarakat->email)
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP connection refused: smtp.gmail.com:587'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context = []) use ($peminjaman) {
                return str_contains($message, 'Gagal mengirim email notifikasi status')
                    && str_contains($message, 'SMTP connection refused')
                    && ($context['request_code'] ?? null) === $peminjaman->nomor_pengajuan;
            });
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_DITOLAK,
                'catatan_petugas' => $alasan,
            ]);

        $response->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
        $response->assertSessionHas('success');

        $fresh = $peminjaman->fresh();
        $this->assertEquals(PeminjamanAula::STATUS_DITOLAK, $fresh->status);
        $this->assertEquals($alasan, $fresh->catatan_petugas);
    }

    /**
     * Skenario 8: Submit berulang (retry / refresh / submit dua kali) -> tidak terjadi pengiriman email ganda.
     */
    public function test_8_submit_berulang_tidak_mengirimkan_email_ganda(): void
    {
        Mail::fake();

        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);

        // Submit pertama: status berubah -> email terkirim 1 kali
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => 'Nomor surat permohonan belum tercantum.',
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));

        // Submit kedua & ketiga (simulasi refresh / klik ulang): status sama -> tidak kirim lagi
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => 'Nomor surat permohonan belum tercantum.',
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));

        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => PeminjamanAula::STATUS_PERLU_PERBAIKAN,
                'catatan_petugas' => 'Nomor surat permohonan belum tercantum.',
            ])
            ->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));

        Mail::assertSent(PengajuanStatusNotificationMail::class, 1);

        // Sertifikat Ditolak berulang
        $certRequest = $this->createCertificateRequest('Diajukan');

        $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Ditolak',
                'rejection_reason' => 'Kegiatan berada di luar wilayah Kecamatan Cikampek.',
            ])
            ->assertRedirect(route('admin.sertifikat.show', $certRequest->id));

        $this->actingAs($this->admin)
            ->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
                'status' => 'Ditolak',
                'rejection_reason' => 'Kegiatan berada di luar wilayah Kecamatan Cikampek.',
            ])
            ->assertRedirect(route('admin.sertifikat.show', $certRequest->id));

        // Total email sepanjang test: 1 untuk Aula + 1 untuk Sertifikat = 2
        Mail::assertSent(PengajuanStatusNotificationMail::class, 2);
    }

    /**
     * Skenario 9: Halaman form admin menampilkan kontrol alasan wajib dan mempertahankan daftar status Sertifikat.
     */
    public function test_9_halaman_form_admin_menampilkan_kontrol_alasan_wajib_dan_mempertahankan_ui(): void
    {
        $peminjaman = $this->createPeminjamanAula(PeminjamanAula::STATUS_MENUNGGU_VERIFIKASI);
        $respAula = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.show', $peminjaman->id));
        $respAula->assertStatus(200);
        $respAula->assertSee('sipafStatusReasonForm');
        $respAula->assertSee('catatan_petugas');
        $respAula->assertSee('Wajib diisi');

        $certRequest = $this->createCertificateRequest('Diajukan');
        $respCert = $this->actingAs($this->admin)->get(route('admin.sertifikat.show', $certRequest->id));
        $respCert->assertStatus(200);
        $respCert->assertSee('sipafStatusReasonForm');
        $respCert->assertSee('rejection_reason');
        $respCert->assertSee('Wajib diisi');
        // Memastikan tidak menambah status Perlu Perbaikan pada Sertifikat sesuai kebutuhan
        $respCert->assertDontSee('value="Perlu Perbaikan"', false);
    }
}

