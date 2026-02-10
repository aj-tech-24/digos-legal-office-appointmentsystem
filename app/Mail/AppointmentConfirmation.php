<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Appointment $appointment
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Request Received - ' . $this->appointment->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $appointment = $this->appointment;

        // Pre-compute services
        $services = [];
        if ($appointment->detected_services) {
            if (isset($appointment->detected_services['primary'])) {
                $services[] = $appointment->detected_services['primary'];
            }
            if (isset($appointment->detected_services['secondary']) && $appointment->detected_services['secondary']) {
                $services[] = $appointment->detected_services['secondary'];
            }
        }

        // Pre-compute document checklist
        $documents = [];
        if ($appointment->document_checklist && count($appointment->document_checklist) > 0) {
            foreach ($appointment->document_checklist as $document) {
                $documents[] = is_array($document) ? ($document['item'] ?? 'Document') : $document;
            }
        }

        return new Content(
            view: 'emails.appointment-confirmation',
            with: [
                'appointment' => $appointment,
                'clientRecord' => $appointment->clientRecord,
                'lawyer' => $appointment->lawyer,
                'services' => $services,
                'documents' => $documents,
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
