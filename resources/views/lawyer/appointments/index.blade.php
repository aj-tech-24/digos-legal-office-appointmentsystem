@extends('layouts.lawyer')

@section('title', 'My Appointments')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">My Appointments</h1>
            <p class="text-muted mb-0 small">View and manage your scheduled appointments</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('lawyer.appointments.index') }}" class="row g-2 align-items-end">

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

                {{-- From Date --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                    <input type="date" class="form-select" name="date_from" value="{{ request('date_from') }}">
                </div>

                {{-- To Date --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                    <input type="date" class="form-select" name="date_to" value="{{ request('date_to') }}">
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('lawyer.appointments.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
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
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Schedule</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Duration</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Complexity</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Status</th>
                            <th class="py-3 pe-4 text-end text-muted small fw-semibold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        @php
                            $statusColors = [
                                'pending'    => 'warning',
                                'confirmed'  => 'info',
                                'in_progress'=> 'primary',
                                'completed'  => 'success',
                                'cancelled'  => 'danger',
                                'no_show'    => 'secondary',
                                'checked_in' => 'dark',
                            ];
                            $color = $statusColors[$appointment->status] ?? 'secondary';
                        @endphp
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
                                    <div>
                                        <div class="fw-semibold small">{{ $appointment->clientRecord->full_name }}</div>
                                        @if($appointment->clientRecord->phone)
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                <i class="bi bi-telephone me-1"></i>{{ $appointment->clientRecord->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Schedule --}}
                            <td>
                                <div class="fw-semibold small">{{ $appointment->start_datetime->format('M d, Y') }}</div>
                                <div class="text-info fw-bold" style="font-size:0.78rem;">
                                    {{ $appointment->start_datetime->format('g:i A') }} – {{ $appointment->end_datetime->format('g:i A') }}
                                </div>
                            </td>

                            {{-- Duration --}}
                            <td class="small text-muted">
                                {{ $appointment->estimated_duration_minutes ?? 30 }} mins
                            </td>

                            {{-- Complexity --}}
                            <td>
                                @if($appointment->complexity_level)
                                    @switch($appointment->complexity_level)
                                        @case('simple')
                                            <span class="badge bg-success rounded-pill">Simple</span>
                                            @break
                                        @case('moderate')
                                            <span class="badge bg-warning text-dark rounded-pill">Moderate</span>
                                            @break
                                        @case('complex')
                                            <span class="badge bg-danger rounded-pill">Complex</span>
                                            @break
                                    @endswitch
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge rounded-pill bg-{{ $color }} text-white px-2 py-1"
                                      style="font-size:0.75rem;">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">

                                    {{-- View (Always) --}}
                                    <a href="{{ route('lawyer.appointments.show', $appointment) }}"
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

                                    {{-- Confirmed Actions (Not yet checked in) --}}
                                    @if($appointment->status === 'confirmed' && !$appointment->checked_in_at)
                                        <form action="{{ route('lawyer.appointments.checkIn', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Check In Client">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('lawyer.appointments.cancel', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Cancel Appointment"
                                                    onclick="return confirm('Cancel this appointment?')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Checked In Actions (confirmed + has checked_in_at) --}}
                                    @if($appointment->status === 'confirmed' && $appointment->checked_in_at)
                                        <span class="badge bg-success me-1" style="font-size:0.65rem;"><i class="bi bi-check me-1"></i>Checked In</span>
                                        <form action="{{ route('lawyer.appointments.start', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Start Consultation">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- In Progress Actions --}}
                                    @if($appointment->status === 'in_progress')
                                        <form action="{{ route('lawyer.appointments.complete', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Complete Consultation">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                        @endforeach
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
                <form action="{{ route('lawyer.appointments.confirm', $appointment->id) }}" method="POST">
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
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Relevant Documents" checked>
                                    <label class="form-check-label small">Relevant Documents</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Timeline of Events" checked>
                                    <label class="form-check-label small">Timeline of Events</label>
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
                            <i class="bi bi-check-circle me-1"></i> Confirm & Send Email
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
                <form action="{{ route('lawyer.appointments.decline', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Decline Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Declining appointment for <strong>{{ $appointment->clientRecord->full_name }}</strong>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Reason for Declining <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="decline_reason" rows="3" required
                                      placeholder="Please provide a reason..."></textarea>
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
@endforeach

@endsection