@extends('layouts.admin')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment: ' . $appointment->reference_number)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Appointment Details</span>
                <span class="badge bg-{{ $appointment->status_color }} fs-6">
                    {{ $appointment->status_label }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Client Information</h6>
                        <p class="mb-1"><strong>{{ $appointment->clientRecord->full_name }}</strong></p>
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $appointment->clientRecord->email }}</p>
                        @if($appointment->clientRecord->phone)
                            <p class="mb-0"><i class="bi bi-telephone me-2"></i>{{ $appointment->clientRecord->phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Schedule</h6>
                        <p class="mb-1"><i class="bi bi-calendar me-2"></i>{{ $appointment->formatted_date }}</p>
                        <p class="mb-1"><i class="bi bi-clock me-2"></i>{{ $appointment->formatted_time_range }}</p>
                        <p class="mb-0"><i class="bi bi-hourglass me-2"></i>{{ $appointment->estimated_duration_minutes }} minutes</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Assigned Lawyer</h6>
                        @if($appointment->lawyer)
                            <p class="mb-1"><strong>{{ $appointment->lawyer->user->name }}</strong></p>
                            <p class="mb-0">
                                @foreach($appointment->lawyer->specializations as $spec)
                                    <span class="badge bg-{{ $spec->pivot->is_primary ? 'primary' : 'secondary' }} me-1">
                                        {{ $spec->name }}
                                    </span>
                                @endforeach
                            </p>
                        @else
                            <p class="text-muted">Not assigned</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Case Classification</h6>
                        <p class="mb-1">
                            <strong>Complexity:</strong>
                            @php
                                $complexityColors = ['simple' => 'success', 'moderate' => 'warning', 'complex' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $complexityColors[$appointment->complexity_level] ?? 'secondary' }}">
                                {{ ucfirst($appointment->complexity_level) }}
                            </span>
                        </p>
                        @if($appointment->detected_services)
                            <p class="mb-0">
                                <strong>Services:</strong>
                                <span class="badge bg-primary">{{ $appointment->detected_services['primary'] ?? 'N/A' }}</span>
                                @if(isset($appointment->detected_services['secondary']) && $appointment->detected_services['secondary'])
                                    <span class="badge bg-secondary">{{ $appointment->detected_services['secondary'] }}</span>
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
                
                <h6 class="text-muted">Case Narrative</h6>
                <div class="bg-light p-3 rounded mb-4">
                    {{ $appointment->narrative }}
                </div>
                
                @if($appointment->professional_summary)
                    <h6 class="text-muted">AI Summary</h6>
                    <div class="bg-info bg-opacity-10 p-3 rounded mb-4">
                        <i class="bi bi-robot me-2"></i>{{ $appointment->professional_summary }}
                    </div>
                @endif
                
                @if($appointment->document_checklist && count($appointment->document_checklist) > 0)
                    <h6 class="text-muted">Required Documents</h6>
                    <div class="row">
                        @foreach($appointment->document_checklist as $doc)
                            <div class="col-md-6 mb-2">
                                <div class="p-2 rounded {{ $doc['required'] ?? false ? 'bg-warning bg-opacity-10' : 'bg-light' }}">
                                    <i class="bi bi-file-earmark me-2"></i>
                                    {{ $doc['item'] ?? 'Document' }}
                                    @if($doc['required'] ?? false)
                                        <span class="badge bg-warning text-dark ms-1">Required</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-journal-text me-2"></i>Add Note
            </div>
            <div class="card-body">
                <form action="{{ route('lawyer.appointments.addNote', $appointment) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note Content</label>
                        <textarea class="form-control" name="content" rows="3" required placeholder="Add your note here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Note
                    </button>
                </form>
            </div>
        </div>

    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Actions
            </div>
            <div class="card-body">
                @if($appointment->status === 'pending')
                    
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        <i class="bi bi-check-circle me-1"></i> Confirm Appointment
                    </button>

                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-lg me-1"></i>Cancel Appointment
                    </button>

                @elseif($appointment->status === 'confirmed')
                    <form action="{{ route('lawyer.appointments.checkIn', $appointment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-person-check me-1"></i>Check In Client
                        </button>
                    </form>
                    
                    <form action="{{ route('lawyer.appointments.start', $appointment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-fill me-1"></i>Start Consultation
                        </button>
                    </form>
                    
                    <form action="{{ route('lawyer.appointments.noShow', $appointment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-person-x me-1"></i>Mark No-Show
                        </button>
                    </form>
                @elseif($appointment->status === 'in_progress')
                    <form action="{{ route('lawyer.appointments.complete', $appointment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i>Complete Consultation
                        </button>
                    </form>
                @else
                    <p class="text-muted text-center mb-0">No actions available</p>
                @endif
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i>Notes & Activity</span>
                <span class="badge bg-secondary">{{ $appointment->clientRecord->entries->count() }}</span>
            </div>
            <div class="card-body">
                @if($appointment->clientRecord->entries->count() > 0)
                    <div class="notes-list">
                        @foreach($appointment->clientRecord->entries as $entry)
                            <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="me-2 flex-shrink-0">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $entry->type_color }} bg-opacity-10" 
                                         style="width: 32px; height: 32px;">
                                        <i class="bi {{ $entry->type_icon }} text-{{ $entry->type_color }} small"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-0 small fw-bold text-truncate">{{ $entry->title }}</h6>
                                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.7rem;">{{ $entry->created_at->format('M j, g:i A') }}</small>
                                    </div>
                                    <span class="badge bg-{{ $entry->type_color }} bg-opacity-10 text-{{ $entry->type_color }} mb-1" style="font-size: 0.65rem;">
                                        {{ $entry->type_label }}
                                    </span>
                                    <p class="mb-1 text-muted small" style="white-space: pre-wrap;">{{ $entry->content }}</p>
                                    @if($entry->creator)
                                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-person me-1"></i>{{ $entry->creator->name }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-journal-text fs-4 d-block mb-2"></i>
                        <p class="mb-0 small">No notes yet.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Quick Info
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Reference</td>
                        <td class="text-end"><code>{{ $appointment->reference_number }}</code></td>
                    </tr>
                    @if($appointment->queue_number)
                        <tr>
                            <td class="text-muted">Queue Number</td>
                            <td class="text-end"><span class="badge bg-dark">#{{ $appointment->queue_number }}</span></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lawyer.appointments.confirm', $appointment->id) }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i> Making this confirmation will send an email to the client.
                    </div>

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label fw-bold">Instructions / Checklist</label>
                        <textarea class="form-control" name="admin_notes" id="admin_notes" rows="6" placeholder="Type instructions here (e.g. Bring your ID, etc)...">{{ $appointment->admin_notes }}</textarea>
                        <small class="text-muted">This message will be included in the email.</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm & Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lawyer.appointments.cancel', $appointment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required 
                                  placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection