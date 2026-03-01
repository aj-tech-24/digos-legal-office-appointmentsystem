@extends('layouts.staff')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Appointment Management</h1>
            <p class="text-muted mb-0 small">Track, manage, and act on all appointments</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('staff.appointments.index') }}">
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted small"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 ps-0"
                                placeholder="Client name or Ref #..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\Appointment::STATUS_LABELS as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lawyer Filter --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Lawyer</label>
                        <select name="lawyer_id" class="form-select">
                            <option value="">All Lawyers</option>
                            @foreach($lawyers as $lawyer)
                                <option value="{{ $lawyer->id }}" {{ request('lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                                    Atty. {{ $lawyer->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Date</label>
                        <input type="date" name="date" class="form-select" value="{{ request('date') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Appointments Table --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                {{ $appointments->total() }} Appointment{{ $appointments->total() != 1 ? 's' : '' }} Found
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 ps-4 text-muted small fw-semibold text-uppercase">Ref #</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Client</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Schedule</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Assigned To</th>
                            <th class="py-3 text-muted small fw-semibold text-uppercase">Status</th>
                            <th class="py-3 pe-4 text-end text-muted small fw-semibold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            {{-- Reference Number --}}
                            <td class="ps-4">
                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $appointment->reference_number }}
                                </a>
                            </td>

                            {{-- Client --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                         style="width:36px;height:36px;font-size:0.85rem;">
                                        {{ strtoupper(substr($appointment->clientRecord->first_name, 0, 1) . substr($appointment->clientRecord->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $appointment->clientRecord->full_name }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            <i class="bi bi-telephone me-1"></i>{{ $appointment->clientRecord->phone }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Schedule --}}
                            <td>
                                <div class="fw-semibold small">{{ $appointment->start_datetime->format('M d, Y') }}</div>
                                <div class="text-info fw-bold" style="font-size:0.78rem;">{{ $appointment->start_datetime->format('h:i A') }}</div>
                            </td>

                            {{-- Assigned Lawyer --}}
                            <td>
                                @if($appointment->lawyer)
                                    <span class="small">Atty. {{ $appointment->lawyer->user->name }}</span>
                                @else
                                    <span class="text-muted fst-italic small">Unassigned</span>
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
                                <span class="badge rounded-pill bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25 px-2 py-1"
                                      style="font-size:0.75rem;">
                                    {{ $appointment->status_label }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">

                                    {{-- View Details (Always) --}}
                                    <a href="{{ route('staff.appointments.show', $appointment->id) }}"
                                       class="btn btn-sm btn-outline-secondary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Pending Actions --}}
                                    @if($appointment->status === 'pending')
                                        <a href="{{ route('staff.appointments.show', $appointment->id) }}"
                                           class="btn btn-sm btn-outline-success" title="Review & Confirm">
                                            <i class="bi bi-check2-circle"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="Decline"
                                                data-bs-toggle="modal" data-bs-target="#declineModal-{{ $appointment->id }}">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif

                                    {{-- Confirmed Actions (Not yet checked in) --}}
                                    @if($appointment->status === 'confirmed' && !$appointment->checked_in_at)
                                        <form action="{{ route('staff.appointments.checkIn', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Check In">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.appointments.cancel', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Cancel"
                                                    onclick="return confirm('Cancel this appointment?')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Checked In Actions (confirmed + has checked_in_at) --}}
                                    @if($appointment->status === 'confirmed' && $appointment->checked_in_at)
                                        <span class="badge bg-success me-1" style="font-size:0.65rem;"><i class="bi bi-check me-1"></i>Checked In</span>
                                        <form action="{{ route('staff.appointments.start', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Start Consultation">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.appointments.noShow', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark No-Show">
                                                <i class="bi bi-person-slash"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- In Progress Actions --}}
                                    @if($appointment->status === 'in_progress')
                                        <form action="{{ route('staff.appointments.complete', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Complete">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>

                                {{-- Decline Modal --}}
                                @if($appointment->status === 'pending')
                                <div class="modal fade text-start" id="declineModal-{{ $appointment->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Decline Appointment</h5>
                                                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('staff.appointments.decline', $appointment->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <div class="modal-body">
                                                    <p class="mb-3">Decline appointment for <strong>{{ $appointment->clientRecord->full_name }}</strong>?</p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                                        <textarea name="decline_reason" class="form-control" rows="3" required
                                                                  placeholder="e.g. Lawyer unavailable, conflict of interest..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                                    <button class="btn btn-danger" type="submit">
                                                        <i class="fas fa-times me-1"></i> Decline Appointment
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No appointments found matching your filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($appointments->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center border-0 py-3">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection