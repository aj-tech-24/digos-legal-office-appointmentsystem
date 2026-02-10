@extends('layouts.admin')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment: ' . $appointment->reference_number)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Appointment Details -->
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
        

    </div>
    
    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Actions
            </div>
            <div class="card-body">
                @if($appointment->status === 'pending')
                    <form action="{{ route('admin.appointments.confirm', $appointment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg me-1"></i>Confirm Appointment
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-lg me-1"></i>Cancel Appointment
                    </button>
                @elseif($appointment->status === 'confirmed')
                    <form action="{{ route('admin.appointments.checkIn', $appointment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-person-check me-1"></i>Check In Client
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.appointments.start', $appointment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-fill me-1"></i>Start Consultation
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.appointments.noShow', $appointment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-person-x me-1"></i>Mark No-Show
                        </button>
                    </form>
                @elseif($appointment->status === 'in_progress')
                    <form action="{{ route('admin.appointments.complete', $appointment) }}" method="POST">
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
        
        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Status Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <p class="mb-0"><strong>Created</strong></p>
                            <small class="text-muted">{{ $appointment->created_at->format('M j, Y g:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($appointment->confirmed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Confirmed</strong></p>
                                <small class="text-muted">{{ $appointment->confirmed_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($appointment->checked_in_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Checked In</strong></p>
                                <small class="text-muted">{{ $appointment->checked_in_at->format('M j, Y g:i A') }}</small>
                                @if($appointment->queue_number)
                                    <br><small>Queue #{{ $appointment->queue_number }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    @if($appointment->started_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Started</strong></p>
                                <small class="text-muted">{{ $appointment->started_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($appointment->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Completed</strong></p>
                                <small class="text-muted">{{ $appointment->completed_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($appointment->cancelled_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Cancelled</strong></p>
                                <small class="text-muted">{{ $appointment->cancelled_at->format('M j, Y g:i A') }}</small>
                                @if($appointment->cancellation_reason)
                                    <br><small>{{ $appointment->cancellation_reason }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quick Info -->
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

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.appointments.cancel', $appointment) }}" method="POST">
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

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1rem;
    }
    
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -24px;
        top: 8px;
        bottom: -8px;
        width: 2px;
        background-color: #dee2e6;
    }
    
    .timeline-item:last-child:before {
        display: none;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        top: 4px;
    }
</style>
@endpush
@endsection
