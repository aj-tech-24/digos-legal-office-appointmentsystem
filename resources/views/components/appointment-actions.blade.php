@props(['appointment'])
<div class="flex space-x-2">
    <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="text-blue-500">View</a>
    {{-- Pwede nimo pun-an diri og Edit or Cancel button --}}
</div>