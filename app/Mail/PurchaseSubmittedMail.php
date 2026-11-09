<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PurchaseSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $feature,
        public string $coupleName,
        public int $amount,
        public int $userId,
        public ?string $proofPath = null,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->feature === 'photobooth' ? 'Add-on Photobooth' : 'Paket Pro';

        return new Envelope(
            subject: "Pembelian {$label} baru menunggu konfirmasi",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.purchase-submitted',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->proofPath) {
            return [];
        }

        $ext = pathinfo($this->proofPath, PATHINFO_EXTENSION);
        $name = 'bukti-transfer-'.(Str::slug($this->coupleName) ?: 'pasangan');

        return [
            Attachment::fromStorageDisk('local', $this->proofPath)
                ->as($ext !== '' ? "{$name}.{$ext}" : $name),
        ];
    }
}
