<?php

namespace App\Mail;

use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InventoryBorrowingSuratFinalMail extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryBorrowing $borrowing;
    public ?InventoryBorrowingDocument $document;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryBorrowing $borrowing, ?InventoryBorrowingDocument $document = null)
    {
        $this->borrowing = $borrowing;
        $this->document = $document ?? $borrowing->activeFinalDocument;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->document && $this->document->version > 1)
            ? 'Pembaruan Dokumen: Surat Persetujuan Peminjaman Inventaris — SIPAF Kecamatan Cikampek'
            : 'Surat Persetujuan Peminjaman Inventaris Telah Terbit — SIPAF Kecamatan Cikampek';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory_borrowing_surat_final',
            with: [
                'borrowing' => $this->borrowing,
                'document' => $this->document,
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
            $this->document &&
            !empty($this->document->file_path) &&
            Storage::disk('local')->exists($this->document->file_path)
        ) {
            $ext = pathinfo($this->document->file_name, PATHINFO_EXTENSION) ?: 'pdf';
            $safeName = 'Surat_Persetujuan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->borrowing->request_code) . '.' . $ext;

            $attachments[] = Attachment::fromStorageDisk('local', $this->document->file_path)
                ->as($safeName);
        }

        return $attachments;
    }
}
