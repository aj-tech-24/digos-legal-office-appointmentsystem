<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     * Accepts Appointment data, Instructions text, and Requirements array.
     */
    public function __construct(
        public Appointment $appointment,
        public ?string $instructions = null,
        public array $requirements = []
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Confirmed - ' . $this->appointment->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Pre-compute services (Just in case you need to display them in the email)
        $services = [];
        if ($this->appointment->detected_services) {
            if (isset($this->appointment->detected_services['primary'])) {
                $services[] = $this->appointment->detected_services['primary'];
            }
            if (isset($this->appointment->detected_services['secondary']) && $this->appointment->detected_services['secondary']) {
                $services[] = $this->appointment->detected_services['secondary'];
            }
        }

        // Return the view with all necessary data
        return new Content(
            view: 'emails.appointment-confirmation',
            with: [
                'appointment'  => $this->appointment,
                'instructions' => $this->instructions,
                'requirements' => $this->requirements,
                'services'     => $services,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}