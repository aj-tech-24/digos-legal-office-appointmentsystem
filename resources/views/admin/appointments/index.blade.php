@extends('layouts.admin')

@section('title', 'Manage Appointments')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Appointments</h1>
            <p class="text-muted mb-0 small">View and manage all system appointments</p>
        </div>
        <div>
            <a href="{{ route('admin.appointments.queue') }}" class="btn btn-primary">
                <i class="bi bi-clock-history me-1"></i> Today's Queue
            </a>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-2 align-items-end">

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed"   {{ request('status') == 'confirmed'   ? 'selected' : '' }}>Confirmed</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed"   {{ request('status') == 'completed'   ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled"   {{ request('status') == 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show"     {{ request('status') == 'no_show'     ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted small"></i>
                        </span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0"
                               name="search" value="{{ request('search') }}"
                               placeholder="Ref # or client name...">
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="bi bi-calendar-check me-2 text-primary"></i>
                {{ $appointments->total() }} Appointment{{ $appointments->total() != 1 ? 's' : '' }} Found
            </span>
        </div>

        @if($appointments->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Appointments Found</h5>
                <p class="text-muted small mb-0">Try adjusting your filters or check back later.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 ps-4 text-muted small fw-semibold text-uppercase">Reference</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Client</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Lawyer</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Status</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Schedule</th>
                            <th class="py-3 pe-4 text-end text-muted small fw-semibold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                {{-- Reference --}}
                                <td class="ps-4">
                                    <code class="bg-light text-dark px-2 py-1 rounded small">
                                        {{ $appointment->reference_number }}
                                    </code>
                                </td>

                                {{-- Client --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                             style="width:36px;height:36px;font-size:0.8rem;">
                                            {{ strtoupper(substr($appointment->clientRecord->first_name, 0, 1) . substr($appointment->clientRecord->last_name, 0, 1)) }}
                                        </div>
                                        <div class="fw-semibold small">{{ $appointment->clientRecord->full_name }}</div>
                                    </div>
                                </td>

                                {{-- Lawyer --}}
                                <td class="small">
                                    @if($appointment->lawyer && $appointment->lawyer->user)
                                        Atty. {{ $appointment->lawyer->user->name }}
                                    @else
                                        <span class="text-muted fst-italic">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending'    => 'warning',
                                            'confirmed'  => 'info',
                                            'in_progress'=> 'primary',
                                            'completed'  => 'success',
                                            'cancelled'  => 'danger',
                                            'no_show'    => 'secondary',
                                            'checked_in' => 'teal',
                                        ];
                                        $color = $statusColors[$appointment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $color }} text-white px-2 py-1"

                                          style="font-size:0.75rem;">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </td>

                                {{-- Schedule --}}
                                <td>
                                    <div class="small fw-semibold">{{ $appointment->start_datetime->format('M d, Y') }}</div>
                                    <div class="text-info fw-bold" style="font-size:0.78rem;">{{ $appointment->start_datetime->format('h:i A') }}</div>
                                </td>

                                {{-- Actions --}}
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-1">

                                        {{-- View Details (Always) --}}
                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Pending Actions --}}
                                        @if($appointment->status === 'pending')
                                            <button class="btn btn-sm btn-outline-success" title="Confirm"
                                                    data-bs-toggle="modal" data-bs-target="#confirmModal-{{ $appointment->id }}">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Decline"
                                                    data-bs-toggle="modal" data-bs-target="#declineModal-{{ $appointment->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif

                                        {{-- Confirmed (Not yet checked in) --}}
                                        @if($appointment->status === 'confirmed' && !$appointment->checked_in_at)
                                            <form action="{{ route('admin.appointments.checkIn', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Check In">
                                                    <i class="bi bi-person-check"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-warning" title="Cancel"
                                                    data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $appointment->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif

                                        {{-- Confirmed & Checked In --}}
                                        @if($appointment->status === 'confirmed' && $appointment->checked_in_at)
                                            <span class="badge bg-success me-1" style="font-size:0.65rem;"><i class="bi bi-check me-1"></i>Checked In</span>
                                            <form action="{{ route('admin.appointments.start', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Start Consultation">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- In Progress Actions --}}
                                        @if($appointment->status === 'in_progress')
                                            <form action="{{ route('admin.appointments.complete', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Complete">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">No appointments found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                {{ $appointments->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @endif
    </div>
</div>

{{-- MODALS --}}
@foreach($appointments as $appointment)

    @if($appointment->status === 'pending')

    {{-- Confirm Modal --}}
    <div class="modal fade" id="confirmModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check2-circle me-2"></i> Confirm Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="bi bi-envelope me-2"></i>
                            Confirming will send an email to <strong>{{ $appointment->clientRecord->first_name }}</strong>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Documents to Bring:</label>
                            <div class="card p-3 bg-light border-0">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Valid Government ID" checked>
                                    <label class="form-check-label small">Valid Government ID</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Relevant Documents" checked>
                                    <label class="form-check-label small">Relevant Documents</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Additional Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3"
                                      placeholder="Any specific instructions for the client..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Confirm & Notify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Decline Modal --}}
    <div class="modal fade" id="declineModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.appointments.decline', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Decline Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small mb-3">Declining appointment for <strong>{{ $appointment->clientRecord->full_name }}</strong>. The client will be notified.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Reason for Declining <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="decline_reason" rows="3" required
                                      placeholder="Reason for declining this appointment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i> Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($appointment->status === 'confirmed')
    <div class="modal fade" id="cancelModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i> Cancel Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Are you sure you want to cancel <strong>{{ $appointment->clientRecord->full_name }}'s</strong> appointment? This cannot be undone.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Reason for Cancellation <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control" name="cancel_reason" rows="3"
                                      placeholder="State the reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-x-circle me-1"></i> Cancel Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Modal --}}
    @if($appointment->status === 'completed')
    <div class="modal fade" id="summaryModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2"></i> Appointment Summary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Reference</dt>
                        <dd class="col-sm-8">
                            <code class="bg-light text-dark px-2 py-1 rounded">{{ $appointment->reference_number }}</code>
                        </dd>

                        <dt class="col-sm-4 text-muted">Client</dt>
                        <dd class="col-sm-8 fw-semibold">{{ $appointment->clientRecord->full_name }}</dd>

                        <dt class="col-sm-4 text-muted">Lawyer</dt>
                        <dd class="col-sm-8">
                            @if($appointment->lawyer && $appointment->lawyer->user)
                                {{ $appointment->lawyer->user->name }}
                                <br><span class="text-muted">{{ $appointment->lawyer->specializations->pluck('name')->join(', ') }}</span>
                            @else
                                <span class="text-muted fst-italic">Unassigned</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-success rounded-pill px-2">Completed</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Start Time</dt>
                        <dd class="col-sm-8">{{ $appointment->start_datetime->format('M d, Y h:i A') }}</dd>

                        <dt class="col-sm-4 text-muted">End Time</dt>
                        <dd class="col-sm-8">{{ $appointment->end_datetime ? $appointment->end_datetime->format('M d, Y h:i A') : '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Consultation Notes</dt>
                        <dd class="col-sm-8">
                            @php
                                $completionEntry = $appointment->clientRecord?->entries
                                    ?->where('appointment_id', $appointment->id)
                                    ->first();
                            @endphp
                            @if($completionEntry)
                                {{ $completionEntry->content }}
                            @else
                                <span class="text-muted fst-italic">No resolution notes.</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-primary">
                        <i class="bi bi-eye me-1"></i> Full Details
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

@endforeach

@endsection