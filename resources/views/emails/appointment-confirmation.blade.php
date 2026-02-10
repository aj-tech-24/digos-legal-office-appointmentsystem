<x-mail::message>
# Appointment Request Received

Dear **{{ $clientRecord->full_name }}**,

Thank you for submitting your appointment request with the Digos City Legal Office. Your request has been received and is now **pending review**.

<x-mail::panel>
**Important:** Your appointment is NOT yet confirmed. Please wait for a confirmation email before visiting our office.
</x-mail::panel>

## Appointment Details

**Reference Number:** {{ $appointment->reference_number }}

**Requested Date:** {{ $appointment->start_datetime->format('l, F j, Y') }}

**Requested Time:** {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}

**Assigned Lawyer:** Atty. {{ $lawyer->user->name ?? 'To be assigned' }}

**Estimated Duration:** {{ $appointment->estimated_duration_minutes ?? 30 }} minutes

**Status:** Pending Review

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

## What's Next?

1. Your request will be reviewed by our staff or assigned lawyer within **1-2 business days**.
2. You will receive a **confirmation email** once your appointment is approved.
3. If there are any issues with your requested schedule, we will contact you.

## Documents to Prepare

Please prepare the following documents for your appointment:

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

- **Do not visit our office until you receive a confirmation email.**
- If you need to cancel your request, please contact us as soon as possible.
- Keep your reference number for tracking purposes.

## Location

**Digos City Legal Office**
City Hall Building, Digos City, Davao del Sur

---

If you have any questions, please contact us at (082) XXX-XXXX.

Thank you for choosing the Digos City Legal Office.

Best regards,<br>
**Digos City Legal Office**
</x-mail::message>
