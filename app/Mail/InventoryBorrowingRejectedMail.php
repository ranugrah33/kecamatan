<?php

namespace App\Mail;

use App\Models\InventoryBorrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryBorrowingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryBorrowing $borrowing;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryBorrowing $borrowing)
    {
        $this->borrowing = $borrowing;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Penolakan Pengajuan Peminjaman Barang Inventaris - ' . $this->borrowing->request_code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory_borrowing_rejected',
            with: [
                'borrowing' => $this->borrowing,
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
        return [];
    }
}

