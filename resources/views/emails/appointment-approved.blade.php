<x-mail::message>
# Appointment Confirmed!

Dear **{{ $clientRecord->full_name }}**,

Great news! Your appointment with the Digos City Legal Office has been **approved and confirmed**.

<x-mail::panel>
Your appointment is now confirmed. Please arrive at our office on the scheduled date and time.
</x-mail::panel>

## Confirmed Appointment Details

**Reference Number:** {{ $appointment->reference_number }}

**Date:** {{ $appointment->start_datetime->format('l, F j, Y') }}

**Time:** {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}

**Assigned Lawyer:** Atty. {{ $lawyer->user->name ?? 'To be assigned' }}

**Estimated Duration:** {{ $appointment->estimated_duration_minutes ?? 30 }} minutes

@if($appointment->detected_services)
@php
    $services = [];
    if (isset($appointment->detected_services['primary'])) {
        $services[] = $appointment->detected_services['primary'];
    }
    if (isset($appointment->detected_services['secondary']) && $appointment->detected_services['secondary']) {
        $services[] = $appointment->detected_services['secondary'];
    }
@endphp
@if(count($services) > 0)
**Services:** {{ implode(', ', $services) }}
@endif
@endif

## What to Bring

Please bring the following documents to your appointment:

@if($appointment->document_checklist && count($appointment->document_checklist) > 0)
@foreach($appointment->document_checklist as $document)
@php
    $docName = is_array($document) ? ($document['item'] ?? 'Document') : $document;
@endphp
- {{ $docName }}
@endforeach
@else
- Valid Government ID
- Any relevant documents related to your case
@endif

## Important Reminders

- Please arrive at least **15 minutes** before your scheduled time.
- If you need to cancel or reschedule, please contact us at least **24 hours** in advance.
- Bring a face mask and observe health protocols.

## Location

**Digos City Legal Office**
City Hall Building, Digos City, Davao del Sur

---

If you have any questions, please contact us at (082) XXX-XXXX.

Thank you for choosing the Digos City Legal Office.

Best regards,<br>
**Digos City Legal Office**
</x-mail::message>
