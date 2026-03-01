@extends('layouts.lawyer')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('lawyer.appointments.index') }}">Appointments</a></li>
                    <li class="breadcrumb-item active">{{ $appointment->reference_number }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Appointment Details</h1>
        </div>
        <div>
            @if($appointment->status === 'pending')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal-{{ $appointment->id }}">
                    <i class="bi bi-check-lg me-1"></i> Confirm Appointment
                </button>

                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declineModal">
                    <i class="bi bi-x-lg me-1"></i> Decline
                </button>
            @elseif($appointment->status === 'confirmed')
                @if($appointment->checked_in_at)
                    <form action="{{ route('lawyer.appointments.start', $appointment) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill me-1"></i> Start Consultation
                        </button>
                    </form>
                @else
                    <span class="badge bg-info fs-6 py-2 px-3">
                        <i class="bi bi-hourglass-split me-1"></i> Waiting for Check-in
                    </span>
                @endif
            @elseif($appointment->status === 'in_progress')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                    <i class="bi bi-check-lg me-1"></i> Complete Consultation
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Appointment Details -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $appointment->status_color }} fs-6 me-2">
                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                            </span>
                            <code class="bg-light text-dark px-2 py-1 rounded">{{ $appointment->reference_number }}</code>
                        </div>
                        <div class="text-end">
                            <strong>{{ $appointment->start_datetime->format('l, F j, Y') }}</strong>
                            <br>
                            <span class="text-muted">
                                {{ $appointment->start_datetime->format('g:i A') }} - 
                                {{ $appointment->end_datetime->format('g:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Narrative -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i> Client's Narrative</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-3 mb-3" style="white-space: pre-wrap;">{{ $appointment->narrative }}</div>
                    
                    @if($appointment->professional_summary)
                    <h6 class="text-muted mb-2"><i class="bi bi-robot me-1"></i> AI Summary</h6>
                    <p class="mb-0">{{ $appointment->professional_summary }}</p>
                    @endif
                </div>
            </div>

            <!-- Detected Services & Complexity -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-tags me-2"></i> Detected Services</h6>
                        </div>                        <div class="card-body">
                            @if($appointment->detected_services)
                                @if(isset($appointment->detected_services['primary']))
                                    <span class="badge bg-primary me-1 mb-1">{{ $appointment->detected_services['primary'] }}</span>
                                @endif
                                @if(isset($appointment->detected_services['secondary']) && $appointment->detected_services['secondary'])
                                    <span class="badge bg-secondary me-1 mb-1">{{ $appointment->detected_services['secondary'] }}</span>
                                @endif
                            @else
                                <span class="text-muted">No services detected</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-speedometer me-2"></i> Complexity & Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Complexity:</strong>
                                @switch($appointment->complexity_level)
                                    @case('simple')
                                        <span class="badge bg-success">Simple</span>
                                        @break
                                    @case('moderate')
                                        <span class="badge bg-warning text-dark">Moderate</span>
                                        @break
                                    @case('complex')
                                        <span class="badge bg-danger">Complex</span>
                                        @break
                                    @default
                                        <span class="text-muted">—</span>
                                @endswitch
                            </div>
                            <div>
                                <strong>Duration:</strong>
                                {{ $appointment->estimated_duration_minutes ?? 30 }} minutes
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Note -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Add Note</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lawyer.appointments.addNote', $appointment) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Note Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="Brief title for this note...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note Content</label>
                            <textarea class="form-control" name="content" rows="3" required placeholder="Detailed notes about the consultation..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Client Info -->
        <div class="col-lg-4">
            <!-- Client Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i> Client Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            {{ strtoupper(substr($appointment->clientRecord->first_name, 0, 1) . substr($appointment->clientRecord->last_name, 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $appointment->clientRecord->full_name }}</h5>
                    </div>

                    <hr>

                    <dl class="mb-0">
                        @if($appointment->clientRecord->email)
                        <dt><i class="bi bi-envelope text-muted me-2"></i>Email</dt>
                        <dd>{{ $appointment->clientRecord->email }}</dd>
                        @endif

                        @if($appointment->clientRecord->phone)
                        <dt><i class="bi bi-telephone text-muted me-2"></i>Phone</dt>
                        <dd>{{ $appointment->clientRecord->phone }}</dd>
                        @endif

                        @if($appointment->clientRecord->address)
                        <dt><i class="bi bi-geo-alt text-muted me-2"></i>Address</dt>
                        <dd>{{ $appointment->clientRecord->address }}</dd>
                        @endif                    </dl>
                </div>
            </div>

            <!-- Notes & Activity -->
            @php
                $appointmentNotes = $appointment->clientRecord->entries->where('appointment_id', $appointment->id);
            @endphp
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i> Notes & Activity</h6>
                    <span class="badge bg-secondary">{{ $appointmentNotes->count() }}</span>
                </div>
                <div class="card-body">
                    @if($appointmentNotes->count() > 0)
                        <div class="notes-list">
                            @foreach($appointmentNotes as $entry)
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
                            <p class="mb-0 small">No notes for this appointment yet.</p>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lawyer.appointments.decline', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Decline Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will decline the appointment. The client will be notified that their appointment was declined.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Declining <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="decline_reason" rows="4" required
                                  placeholder="Please provide a reason for declining this appointment..."></textarea>
                        <div class="form-text">This reason will be recorded in the client's timeline.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Decline Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lawyer.appointments.complete', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Consultation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This will mark the consultation as completed and add a record to the client's timeline.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes (Optional)</label>
                        <textarea class="form-control" name="resolution_notes" rows="4" 
                                  placeholder="Brief summary of the consultation outcome..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Complete Consultation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal-{{ $appointment->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('lawyer.appointments.confirm', $appointment->id) }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i> 
                        Making this confirmation will send an email to <strong>{{ $appointment->clientRecord->first_name }}</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Requirements to bring:</label>
                        <div class="card p-2 bg-light border" style="max-height: 200px; overflow-y: auto;">
                            {{-- Standard Requirements --}}
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="requirements[]" value="Valid ID" checked>
                                <label class="form-check-label">Valid ID</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="requirements[]" value="Proof of Residency">
                                <label class="form-check-label">Proof of Residency</label>
                            </div>

                            {{-- AI-detected document checklist --}}
                            @if($appointment->document_checklist)
                                @foreach($appointment->document_checklist as $doc)
                                    @php $val = is_array($doc) ? ($doc['item'] ?? $doc) : $doc; @endphp
                                    @if($val !== 'Valid ID')
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="requirements[]" value="{{ $val }}" checked>
                                            <label class="form-check-label">{{ $val }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2">
                            <label class="small text-muted">Add custom requirement:</label>
                            <input type="text" name="requirements[]" class="form-control form-control-sm" placeholder="E.g., Affidavit of Loss">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label fw-bold">Additional Instructions / Notes</label>
                        <textarea name="instructions" id="instructions" class="form-control" rows="3" placeholder="Type specific instructions here (e.g. Please bring your land title, etc)..."></textarea>
                        <small class="text-muted">This message will be included in the email.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Confirm & Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
