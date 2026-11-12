<?php

namespace App\Services;

use App\Mail\PengajuanStatusNotificationMail;
use App\Models\CertificateRequest;
use App\Models\PeminjamanAula;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PengajuanStatusNotificationService
{
    /**
     * Aturan validasi alasan/catatan admin: wajib diisi (non-kosong) untuk status tertentu,
     * dan tetap opsional (nullable|string) untuk status lainnya.
     */
    public static function reasonRules(?string $status, array $triggerStatuses, string $message): array|string
    {
        if (!in_array($status, $triggerStatuses, true)) {
            return 'nullable|string';
        }

        return [
            'required',
            'string',
            static function (string $attribute, mixed $value, \Closure $fail) use ($message): void {
                if (trim((string) $value) === '') {
                    $fail($message);
                }
            },
        ];
    }

    /**
     * Susun pesan flash sukses untuk admin berdasarkan hasil pengiriman email.
     */
    public static function buildStatusSuccessMessage(array $notifResult): string
    {
        if (!empty($notifResult['sent'])) {
            return 'Status pengajuan berhasil diperbarui dan email pemberitahuan telah dikirim ke pemohon.';
        }

        if (($notifResult['reason'] ?? null) === 'invalid_email') {
            return 'Status pengajuan berhasil diperbarui! Namun email pemberitahuan tidak terkirim karena email pemohon kosong atau tidak valid.';
        }

        if (($notifResult['reason'] ?? null) === 'smtp_error') {
            return 'Status pengajuan berhasil diperbarui! Namun email pemberitahuan gagal dikirim karena gangguan server email.';
        }

        return 'Status pengajuan berhasil diperbarui!';
    }

    /**
     * Kirim notifikasi email untuk Peminjaman Aula ketika status berubah
     * menjadi 'Perlu Perbaikan' atau 'Ditolak'.
     *
     * @return array{attempted: bool, sent: bool, reason: ?string}
     */
    public function notifyPeminjamanAulaIfNeeded(PeminjamanAula $peminjaman, ?string $oldStatus): array
    {
        $newStatus = $peminjaman->status;
        $targetStatuses = [
            PeminjamanAula::STATUS_PERLU_PERBAIKAN,
            PeminjamanAula::STATUS_DITOLAK,
        ];

        if ($oldStatus === $newStatus || !in_array($newStatus, $targetStatuses, true)) {
            return ['attempted' => false, 'sent' => false, 'reason' => 'not_applicable'];
        }

        $peminjaman->loadMissing('user');

        $tanggalFormatted = '-';
        if (!empty($peminjaman->tanggal)) {
            $tanggalFormatted = Carbon::parse($peminjaman->tanggal)->translatedFormat('l, d F Y');
        }

        $waktuFormatted = '-';
        if (!empty($peminjaman->jam_mulai) && !empty($peminjaman->jam_selesai)) {
            $waktuFormatted = substr((string) $peminjaman->jam_mulai, 0, 5) . ' - ' . substr((string) $peminjaman->jam_selesai, 0, 5) . ' WIB';
        }

        $followUpInfo = $newStatus === PeminjamanAula::STATUS_DITOLAK
            ? 'Apabila Anda memerlukan penjelasan lebih lanjut mengenai penolakan ini atau ingin memilih jadwal alternatif yang tersedia, silakan hubungi petugas Kantor Kecamatan Cikampek pada jam kerja operasional atau ajukan permohonan baru melalui website SIPAF.'
            : null;

        $resubmitUrl = $newStatus === PeminjamanAula::STATUS_PERLU_PERBAIKAN
            ? route('masyarakat.peminjaman_aula.create')
            : null;

        $mail = new PengajuanStatusNotificationMail(
            status: $newStatus,
            requestCode: (string) $peminjaman->nomor_pengajuan,
            serviceName: 'Peminjaman Aula',
            applicantName: $peminjaman->user->name ?? $peminjaman->penanggung_jawab ?? 'Pemohon',
            adminNote: trim((string) ($peminjaman->catatan_petugas ?? '')),
            actionUrl: route('masyarakat.peminjaman_aula.show', $peminjaman->id),
            details: [
                ['label' => 'Nama Kegiatan', 'value' => $peminjaman->nama_kegiatan],
                ['label' => 'Jenis Kegiatan', 'value' => $peminjaman->jenis_kegiatan],
                ['label' => 'Tanggal Kegiatan', 'value' => $tanggalFormatted],
                ['label' => 'Waktu Pelaksanaan', 'value' => $waktuFormatted],
                ['label' => 'Lokasi', 'value' => 'Aula Kantor Kecamatan Cikampek'],
            ],
            followUpInfo: $followUpInfo,
            resubmitUrl: $resubmitUrl,
        );

        return $this->dispatchEmail(
            recipientEmail: $peminjaman->user->email ?? null,
            mail: $mail,
            context: [
                'service' => 'peminjaman_aula',
                'submission_id' => $peminjaman->id,
                'request_code' => $peminjaman->nomor_pengajuan,
                'user_id' => $peminjaman->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    /**
     * Kirim notifikasi email untuk Pengajuan Sertifikat / Piagam ketika status berubah
     * menjadi 'Ditolak'.
     *
     * @return array{attempted: bool, sent: bool, reason: ?string}
     */
    public function notifyCertificateRequestIfNeeded(CertificateRequest $certRequest, ?string $oldStatus): array
    {
        $newStatus = $certRequest->status;

        if ($oldStatus === $newStatus || $newStatus !== 'Ditolak') {
            return ['attempted' => false, 'sent' => false, 'reason' => 'not_applicable'];
        }

        $certRequest->loadMissing('user');

        $activityDateFormatted = '-';
        if (!empty($certRequest->activity_date)) {
            $activityDateFormatted = Carbon::parse($certRequest->activity_date)->translatedFormat('d F Y');
        }

        $docType = $certRequest->document_type ?: 'Sertifikat / Piagam';

        $mail = new PengajuanStatusNotificationMail(
            status: $newStatus,
            requestCode: (string) $certRequest->request_code,
            serviceName: 'Pengajuan ' . $docType,
            applicantName: $certRequest->applicant_name ?: ($certRequest->user->name ?? 'Pemohon'),
            adminNote: trim((string) ($certRequest->rejection_reason ?? '')),
            actionUrl: route('masyarakat.sertifikat.show', $certRequest->id),
            details: [
                ['label' => 'Jenis Dokumen', 'value' => $docType],
                ['label' => 'Nama Kegiatan', 'value' => $certRequest->activity_name],
                ['label' => 'Tanggal Kegiatan', 'value' => $activityDateFormatted],
                ['label' => 'Tempat Kegiatan', 'value' => $certRequest->activity_place],
            ],
            followUpInfo: 'Apabila Anda memerlukan penjelasan lebih lanjut atau ingin melengkapi persyaratan untuk mengajukan permohonan baru, silakan hubungi petugas Kantor Kecamatan Cikampek pada jam kerja operasional atau ajukan kembali melalui website SIPAF.',
        );

        return $this->dispatchEmail(
            recipientEmail: $certRequest->user->email ?? null,
            mail: $mail,
            context: [
                'service' => 'sertifikat',
                'submission_id' => $certRequest->id,
                'request_code' => $certRequest->request_code,
                'user_id' => $certRequest->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    /**
     * Validasi alamat email penerima, kirim email dengan try-catch aman, dan catat log.
     *
     * @return array{attempted: bool, sent: bool, reason: ?string}
     */
    protected function dispatchEmail(?string $recipientEmail, PengajuanStatusNotificationMail $mail, array $context): array
    {
        $requestCode = $context['request_code'] ?? '-';
        $newStatus = $context['new_status'] ?? '-';

        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning(
                "Pengiriman email notifikasi status ({$newStatus}) dilewati: email pemohon kosong atau tidak valid pada pengajuan {$requestCode}.",
                $context
            );

            return ['attempted' => false, 'sent' => false, 'reason' => 'invalid_email'];
        }

        try {
            Mail::to($recipientEmail)->send($mail);

            Log::info(
                "Email notifikasi status ({$newStatus}) untuk pengajuan {$requestCode} berhasil dikirim ke {$recipientEmail}.",
                array_merge($context, ['recipient_email' => $recipientEmail])
            );

            return ['attempted' => true, 'sent' => true, 'reason' => null];
        } catch (\Throwable $e) {
            Log::error(
                "Gagal mengirim email notifikasi status ({$newStatus}) untuk pengajuan {$requestCode} ke {$recipientEmail}: " . $e->getMessage(),
                array_merge($context, [
                    'recipient_email' => $recipientEmail,
                    'error' => $e->getMessage(),
                ])
            );

            return ['attempted' => true, 'sent' => false, 'reason' => 'smtp_error'];
        }
    }
}
