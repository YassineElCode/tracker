<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEarlierDateFound extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $earlierDatums;

    public string $city;

    public function __construct(array $earlierDatums, string $city)
    {
        $this->earlierDatums = $earlierDatums;
        $this->city = $city;
    }

    public function envelope(): Envelope
    {
        $subject = 'New Earlier Date Detected';

        if (! empty($this->city)) {
            $subject .= " in {$this->city}";
        }

        $subject .= ' - '.now()->format('d/m H:i');

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_earlier_date',
            with: [
                'earlierDatums' => $this->earlierDatums,
                'city' => $this->city,
            ],
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
