<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentApproved extends Mailable
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
            subject: 'Appointment Confirmed - ' . $this->appointment->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $appointment = $this->appointment;

        // Services Logic
        $services = [];
        if ($appointment->detected_services) {
            if (isset($appointment->detected_services['primary'])) {
                $services[] = $appointment->detected_services['primary'];
            }
            if (isset($appointment->detected_services['secondary']) && $appointment->detected_services['secondary']) {
                $services[] = $appointment->detected_services['secondary'];
            }
        }

        // Logic para sa Documents (Checklist)
        // Check if admin_notes has the list (from the controller confirmation)
        $documentsArray = [];
        if (!empty($appointment->admin_notes)) {
            // Split per line
            $documentsArray = array_filter(explode("\n", $appointment->admin_notes));
        } else {
            // Fallback sa AI checklist if admin notes are empty
            if ($appointment->document_checklist && count($appointment->document_checklist) > 0) {
                foreach ($appointment->document_checklist as $document) {
                    $documentsArray[] = is_array($document) ? ($document['item'] ?? 'Document') : $document;
                }
            }
        }

        return new Content(
            view: 'emails.appointment-approved',
            with: [
                'appointment' => $appointment,
                'clientRecord' => $appointment->clientRecord,
                'lawyer' => $appointment->lawyer,
                'services' => $services,
                'documents' => $documentsArray, // Gamiton ni sa @foreach sa Blade
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