<?php

namespace App\Mail;

use App\Models\PeminjamanAula;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PeminjamanAulaSuratFinalMail extends Mailable
{
    use Queueable, SerializesModels;

    public PeminjamanAula $peminjaman;

    /**
     * Create a new message instance.
     */
    public function __construct(PeminjamanAula $peminjaman)
    {
        $this->peminjaman = $peminjaman;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Izin Peminjaman Aula Tersedia - ' . $this->peminjaman->nomor_pengajuan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.peminjaman_aula_surat_final',
            with: [
                'peminjaman' => $this->peminjaman,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (
            !empty($this->peminjaman->file_surat_final) &&
            Storage::disk('public')->exists($this->peminjaman->file_surat_final)
        ) {
            $ext = pathinfo($this->peminjaman->file_surat_final, PATHINFO_EXTENSION) ?: 'pdf';
            $safeName = 'Surat_Izin_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->peminjaman->nomor_pengajuan) . '.' . $ext;

            $attachments[] = Attachment::fromStorageDisk('public', $this->peminjaman->file_surat_final)
                ->as($safeName);
        }

        return $attachments;
    }
}

