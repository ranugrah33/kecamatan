<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanStatusNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $status;
    public string $requestCode;
    public string $serviceName;
    public string $applicantName;
    public string $adminNote;
    public string $actionUrl;
    public array $details;
    public ?string $followUpInfo;
    public ?string $resubmitUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $status,
        string $requestCode,
        string $serviceName,
        string $applicantName,
        string $adminNote,
        string $actionUrl,
        array $details = [],
        ?string $followUpInfo = null,
        ?string $resubmitUrl = null,
    ) {
        $this->status = $status;
        $this->requestCode = $requestCode;
        $this->serviceName = $serviceName;
        $this->applicantName = $applicantName;
        $this->adminNote = $adminNote;
        $this->actionUrl = $actionUrl;
        $this->details = $details;
        $this->followUpInfo = $followUpInfo;
        $this->resubmitUrl = $resubmitUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'Perlu Perbaikan'
            ? "[SIPAF] Pengajuan Anda Memerlukan Perbaikan - {$this->requestCode}"
            : "[SIPAF] Pemberitahuan Penolakan Pengajuan - {$this->requestCode}";

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
            view: 'emails.pengajuan_status_notification',
            with: [
                'status' => $this->status,
                'requestCode' => $this->requestCode,
                'serviceName' => $this->serviceName,
                'applicantName' => $this->applicantName,
                'adminNote' => $this->adminNote,
                'actionUrl' => $this->actionUrl,
                'details' => $this->details,
                'followUpInfo' => $this->followUpInfo,
                'resubmitUrl' => $this->resubmitUrl,
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

