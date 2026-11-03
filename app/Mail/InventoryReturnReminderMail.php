<?php

namespace App\Mail;

use App\Models\InventoryBorrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class InventoryReturnReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryBorrowing $borrowing;
    public ?Carbon $checkDate;
    public bool $isTest;

    /**
     * Create a new message instance.
     */
    public function __construct(InventoryBorrowing $borrowing, ?Carbon $checkDate = null, bool $isTest = false)
    {
        $this->borrowing = $borrowing;
        $this->checkDate = $checkDate ?? Carbon::now(config('app.timezone', 'Asia/Jakarta'))->startOfDay();
        $this->isTest = $isTest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isTest
            ? '[TEST SIPAF] Reminder Pengembalian Barang Inventaris'
            : 'Pengingat Pengembalian Barang Inventaris - Kecamatan Cikampek';

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
            view: 'emails.inventory_return_reminder',
            with: [
                'borrowing' => $this->borrowing,
                'checkDate' => $this->checkDate,
                'isTest' => $this->isTest,
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
