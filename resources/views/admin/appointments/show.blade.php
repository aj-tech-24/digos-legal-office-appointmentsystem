@extends('layouts.admin')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    {{-- Header & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
                    <li class="breadcrumb-item active">{{ $appointment->reference_number }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Appointment Details</h1>
        </div>
        <div>
            {{-- Action Buttons based on Status --}}
            @if($appointment->status === 'pending')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                    <i class="bi bi-check-lg me-1"></i> Confirm Appointment
                </button>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declineModal">
                    <i class="bi bi-x-lg me-1"></i> Decline
                </button>
            @elseif($appointment->status === 'confirmed')
                <form action="{{ route('admin.appointments.start', $appointment) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i> Start Consultation
                    </button>
                </form>
            @elseif($appointment->status === 'ongoing') {{-- Fixed: Matches Controller --}}
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                    <i class="bi bi-check-lg me-1"></i> Complete Consultation
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Main Details --}}
        <div class="col-lg-8">
            
            {{-- Status Card --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : ($appointment->status == 'ongoing' ? 'primary' : 'secondary')) }} fs-6 me-2">
                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                            </span>
                            <code class="bg-light text-dark px-2 py-1 rounded">{{ $appointment->reference_number }}</code>
                        </div>
                        <div class="text-end">
                            {{-- Uses start_datetime from Controller logic --}}
                            <strong>{{ \Carbon\Carbon::parse($appointment->start_datetime)->format('l, F j, Y') }}</strong>
                            <br>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('g:i A') }}
                                @if($appointment->end_datetime)
                                    - {{ \Carbon\Carbon::parse($appointment->end_datetime)->format('g:i A') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Narrative Section --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i> Client's Narrative</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-3 mb-3" style="white-space: pre-wrap;">{{ $appointment->narrative ?? 'No narrative provided.' }}</div>
                    
                    @if($appointment->professional_summary)
                    <div class="alert alert-info d-flex align-items-start mb-0">
                        <i class="bi bi-robot me-2 mt-1"></i>
                        <div>
                            <strong>AI Summary:</strong>
                            <p class="mb-0 small">{{ $appointment->professional_summary }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Complexity & Services --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-tags me-2"></i> Detected Services</h6>
                        </div>
                        <div class="card-body">
                            @if($appointment->detected_services)
                                @if(isset($appointment->detected_services['primary']))
                                    <span class="badge bg-primary me-1 mb-1">{{ $appointment->detected_services['primary'] }}</span>
                                @endif
                                @if(isset($appointment->detected_services['secondary']))
                                    <span class="badge bg-secondary me-1 mb-1">{{ $appointment->detected_services['secondary'] }}</span>
                                @endif
                            @else
                                <span class="text-muted">No specific services detected</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-speedometer me-2"></i> Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Complexity:</strong>
                                <span class="badge bg-{{ $appointment->complexity_level == 'complex' ? 'danger' : ($appointment->complexity_level == 'moderate' ? 'warning' : 'success') }}">
                                    {{ ucfirst($appointment->complexity_level ?? 'Normal') }}
                                </span>
                            </div>
                            <div>
                                <strong>Duration:</strong>
                                {{ $appointment->estimated_duration_minutes ?? 60 }} minutes
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Document Checklist (Displayed when confirmed/ongoing) --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Document Checklist</h5>
                    <small class="text-muted ms-4">Documents required for this case type.</small>
                </div>
                <div class="card-body">
                    @if($appointment->document_checklist && count($appointment->document_checklist) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($appointment->document_checklist as $document)
                            @php
                                $docName = is_array($document) ? ($document['item'] ?? 'Unknown Document') : $document;
                                $docRequired = is_array($document) ? ($document['required'] ?? false) : false;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <input class="form-check-input me-2" type="checkbox" disabled>
                                    <span class="fw-bold">{{ $docName }}</span>
                                </div>
                                @if($docRequired) <span class="badge bg-danger">Required</span> @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center my-3">No specific documents listed.</p>
                    @endif
                </div>
            </div>

            {{-- Notes Section (With Filter & Link) --}}
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Case Notes</h5>
                    <div class="input-group input-group-sm w-auto">
                        <span class="input-group-text"><i class="bi bi-filter"></i> Date</span>
                        <input type="date" class="form-control" id="noteDateFilter" onchange="filterNotes()">
                        <button class="btn btn-outline-secondary" onclick="resetNoteFilter()">Reset</button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Add Note Form --}}
                    <form action="{{ route('admin.appointments.addNote', $appointment->id) }}" method="POST" class="mb-4 border-bottom pb-4">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-bold small">New Note Content</label>
                            <textarea class="form-control" name="content" rows="3" required placeholder="Enter detailed case notes..."></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            {{-- UPDATED: Dynamic Dropdown using $clientBookingDates --}}
                            <select class="form-select form-select-sm w-50" name="linked_booking_date">
                                <option value="" selected>Link to Booking Date (Optional)</option>
                                @if(isset($clientBookingDates) && count($clientBookingDates) > 0)
                                    @foreach($clientBookingDates as $date)
                                        <option value="{{ $date->start_datetime }}">
                                            {{ \Carbon\Carbon::parse($date->start_datetime)->format('M d, Y') }} (Ref: {{ $date->reference_number }})
                                        </option>
                                    @endforeach
                                @else
                                    {{-- Fallback --}}
                                    <option value="{{ $appointment->start_datetime }}">
                                        Current: {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('M d, Y') }}
                                    </option>
                                @endif
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add Note
                            </button>
                        </div>
                    </form>

                    {{-- Notes List --}}
                    <div id="notesContainer">
                        {{-- Use ClientRecord entries if they exist --}}
                        @if(optional($appointment->clientRecord)->entries && $appointment->clientRecord->entries->count() > 0)
                            @foreach($appointment->clientRecord->entries as $entry)
                                {{-- Added data-date for JS filter --}}
                                <div class="note-item d-flex mb-3 pb-3 border-bottom" data-date="{{ $entry->created_at->format('Y-m-d') }}">
                                    <div class="me-3 text-center" style="min-width: 60px;">
                                        <div class="bg-light rounded p-1 border">
                                            <small class="d-block fw-bold text-muted">{{ $entry->created_at->format('M') }}</small>
                                            <span class="fs-5 fw-bold">{{ $entry->created_at->format('d') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1 fw-bold">{{ $entry->title ?? 'Note' }}</h6>
                                            <small class="text-muted">{{ $entry->created_at->format('h:i A') }}</small>
                                        </div>
                                        <p class="mb-1 text-muted small">{{ $entry->content }}</p>
                                        @if($entry->linked_booking_date)
                                            <span class="badge bg-light text-dark border mt-1">
                                                <i class="bi bi-link-45deg"></i> Linked to: {{ \Carbon\Carbon::parse($entry->linked_booking_date)->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">No notes found for this client history.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Side Info --}}
        <div class="col-lg-4">
            
            {{-- Assigned Lawyer --}}
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i> Assigned Lawyer</h5>
                </div>
                <div class="card-body">
                    @if($appointment->lawyer)
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">
                                {{ strtoupper(substr($appointment->lawyer->user->first_name ?? 'L', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $appointment->lawyer->user->first_name ?? '' }} {{ $appointment->lawyer->user->last_name ?? '' }}</h6>
                                <small class="text-muted">{{ $appointment->lawyer->specialization ?? 'Legal Counsel' }}</small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0 text-center">
                            <i class="bi bi-exclamation-circle me-1"></i> No Lawyer Assigned
                        </div>
                    @endif
                </div>
            </div>

            {{-- Client Information --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i> Client Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            {{ strtoupper(substr(optional($appointment->clientRecord)->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ optional($appointment->clientRecord)->first_name }} {{ optional($appointment->clientRecord)->last_name }}</h5>
                    </div>

                    <hr>

                    <dl class="mb-0 row">
                        <dt class="col-sm-4 text-muted small">Email</dt>
                        <dd class="col-sm-8 small text-break">{{ optional($appointment->clientRecord)->email ?? 'N/A' }}</dd>

                        <dt class="col-sm-4 text-muted small">Phone</dt>
                        <dd class="col-sm-8 small">{{ optional($appointment->clientRecord)->phone ?? 'N/A' }}</dd>

                        <dt class="col-sm-4 text-muted small">Address</dt>
                        <dd class="col-sm-8 small">
                            {{ optional($appointment->clientRecord)->address ?? '' }}
                            @if(optional($appointment->clientRecord)->barangay)
                                <br><span class="badge bg-light text-dark border mt-1">{{ $appointment->clientRecord->barangay }}</span>
                            @endif
                            @if(empty($appointment->clientRecord->address) && empty($appointment->clientRecord->barangay))
                                <span class="text-danger fst-italic">Not Provided</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODALS SECTION --}}

{{-- 1. Decline Modal --}}
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.decline', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Decline Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i> This will notify the client.
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-danger fw-bold">Reason for Declining *</label>
                        <textarea class="form-control" name="decline_reason" rows="3" required placeholder="Please state the reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Confirm Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Confirming will generate an official schedule and notify the client.
                    </div>
                    
                    {{-- Lawyer-defined Requirements Checklist --}}
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
                            
                            {{-- Existing Requirements if any --}}
                            @if($appointment->document_checklist)
                                @foreach($appointment->document_checklist as $doc)
                                    @php $val = is_array($doc) ? ($doc['item'] ?? $doc) : $doc; @endphp
                                    {{-- Skip Valid ID as it's already above --}}
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
                        <label class="form-label fw-bold">Additional Instructions</label>
                        <textarea name="instructions" class="form-control" rows="2" placeholder="E.g., Please arrive 10 mins early..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm & Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. Complete Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.complete', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Consultation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes / Outcome</label>
                        <textarea class="form-control" name="resolution_notes" rows="4" placeholder="Summary of the consultation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script for Note Filtering --}}
<script>
    function filterNotes() {
        var inputDate = document.getElementById("noteDateFilter").value;
        var notes = document.getElementsByClassName("note-item");
        
        for (var i = 0; i < notes.length; i++) {
            var noteDate = notes[i].getAttribute("data-date");
            if (inputDate === "" || noteDate === inputDate) {
                notes[i].style.display = "flex";
            } else {
                notes[i].style.display = "none";
            }
        }
    }

    function resetNoteFilter() {
        document.getElementById("noteDateFilter").value = "";
        filterNotes();
    }
</script>

@endsection