@extends('layouts.staff')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('staff.appointments.index') }}">Appointments</a></li>
                    <li class="breadcrumb-item active">{{ $appointment->reference_number }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Appointment Details</h1>
        </div>
        <div>
            @if($appointment->status === 'pending')
                <form action="{{ route('staff.appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Confirm Appointment
                    </button>
                </form>
            @endif
            
            @if($appointment->status === 'confirmed' && !$appointment->checked_in_at)
                <form action="{{ route('staff.appointments.checkIn', $appointment) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Check In Client
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
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
                            @if($appointment->queue_number)
                                <span class="badge bg-primary ms-2">Queue #{{ $appointment->queue_number }}</span>
                            @endif
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
                    <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i> Client's Concern</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-3" style="white-space: pre-wrap;">{{ $appointment->narrative }}</div>
                    
                    @if($appointment->professional_summary)
                    <hr>
                    <h6 class="text-muted mb-2"><i class="bi bi-robot me-1"></i> AI Summary</h6>
                    <p class="mb-0">{{ $appointment->professional_summary }}</p>
                    @endif
                </div>
            </div>

            <!-- Services & Complexity -->
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
                            <h6 class="mb-0"><i class="bi bi-speedometer me-2"></i> Case Details</h6>
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
            </div>            <!-- Document Checklist -->
            @if($appointment->document_checklist && count($appointment->document_checklist) > 0)
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Document Checklist</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($appointment->document_checklist as $document)
                        @php
                            $docName = is_array($document) ? ($document['item'] ?? 'Unknown Document') : $document;
                            $docRequired = is_array($document) ? ($document['required'] ?? false) : false;
                            $docDescription = is_array($document) ? ($document['description'] ?? '') : '';
                        @endphp
                        <li class="list-group-item d-flex align-items-start">
                            <i class="bi bi-file-earmark text-muted me-2 mt-1"></i>
                            <div>
                                <strong>{{ $docName }}</strong>
                                @if($docRequired)
                                    <span class="badge bg-danger ms-1">Required</span>
                                @endif
                                @if($docDescription)
                                    <div class="small text-muted">{{ $docDescription }}</div>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Client Info -->
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
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Lawyer Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Assigned Lawyer</h5>
                </div>
                <div class="card-body">
                    @if($appointment->lawyer)
                        <h6>Atty. {{ $appointment->lawyer->user->name }}</h6>
                        @if($appointment->lawyer->specializations->count() > 0)
                            <div class="mb-2">
                                @foreach($appointment->lawyer->specializations as $spec)
                                    <span class="badge bg-secondary">{{ $spec->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Timeline</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Booked</small><br>
                            <strong>{{ $appointment->created_at->format('M d, Y g:i A') }}</strong>
                        </li>
                        @if($appointment->confirmed_at)
                        <li class="mb-2">
                            <small class="text-muted">Confirmed</small><br>
                            <strong>{{ $appointment->confirmed_at->format('M d, Y g:i A') }}</strong>
                        </li>
                        @endif
                        @if($appointment->checked_in_at)
                        <li class="mb-2">
                            <small class="text-success">Checked In</small><br>
                            <strong>{{ $appointment->checked_in_at->format('M d, Y g:i A') }}</strong>
                        </li>
                        @endif
                        @if($appointment->started_at)
                        <li class="mb-2">
                            <small class="text-primary">Started</small><br>
                            <strong>{{ $appointment->started_at->format('M d, Y g:i A') }}</strong>
                        </li>
                        @endif
                        @if($appointment->completed_at)
                        <li class="mb-2">
                            <small class="text-success">Completed</small><br>
                            <strong>{{ $appointment->completed_at->format('M d, Y g:i A') }}</strong>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
