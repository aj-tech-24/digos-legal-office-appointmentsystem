@props(['appointment'])
<div class="dropdown">
    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="actionDropdown-{{ $appointment->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="actionDropdown-{{ $appointment->id }}">
        <li>
            <a class="dropdown-item" href="{{ route('admin.appointments.show', $appointment->id) }}">
                <i class="bi bi-eye me-1"></i> View
            </a>
        </li>
        @if($appointment->status === 'pending')
            <li>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#confirmModal-{{ $appointment->id }}">
                    <i class="bi bi-check2-circle me-1"></i> Confirm
                </a>
            </li>
            <li>
                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#declineModal-{{ $appointment->id }}">
                    <i class="bi bi-x-circle me-1"></i> Decline
                </a>
            </li>
        @elseif($appointment->status === 'confirmed')
            <li>
                <a class="dropdown-item text-warning" href="#">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
            </li>
        @elseif($appointment->status === 'ongoing')
            <li>
                <a class="dropdown-item text-success" href="#">
                    <i class="bi bi-check2 me-1"></i> Complete
                </a>
            </li>
        @endif
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#summaryModal-{{ $appointment->id }}">
                <i class="bi bi-file-earmark-text me-1"></i> View Summary
            </a>
        </li>
    </ul>
</div>

{{-- Appointment Summary Modal --}}
<div class="modal fade" id="summaryModal-{{ $appointment->id }}" tabindex="-1" aria-labelledby="summaryModalLabel-{{ $appointment->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="summaryModalLabel-{{ $appointment->id }}">Appointment Summary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <strong>Reference:</strong> <code class="bg-light text-dark px-2 py-1 rounded">{{ $appointment->reference_number }}</code>
                </div>
                <div class="mb-2">
                    <strong>Client:</strong> {{ $appointment->clientRecord->full_name }}
                </div>
                <div class="mb-2">
                    <strong>Lawyer:</strong> 
                    @if($appointment->lawyer && $appointment->lawyer->user)
                        {{ $appointment->lawyer->user->name }}
                        @if($appointment->lawyer->specializations->count())
                            <span class="text-muted">({{ $appointment->lawyer->specializations->pluck('name')->join(', ') }})</span>
                        @endif
                    @else
                        <span class="text-muted fst-italic">Unassigned</span>
                    @endif
                </div>
                <div class="mb-2">
                    <strong>Status:</strong> <x-status-badge :status="$appointment->status" />
                </div>
                <div class="mb-2">
                    <strong>Start:</strong> {{ $appointment->start_datetime->format('Y-m-d h:i A') }}
                </div>
                <div class="mb-2">
                    <strong>End:</strong> {{ $appointment->end_datetime ? $appointment->end_datetime->format('Y-m-d h:i A') : '-' }}
                </div>
                <div class="mb-2">
                    <strong>Notes:</strong> <span class="text-muted">{{ $appointment->notes ?? 'None' }}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>