@extends('layouts.lawyer')

@section('title', 'My Appointments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Appointments</h1>
            <p class="text-muted mb-0">View and manage your scheduled appointments</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('lawyer.appointments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('lawyer.appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="card">
        <div class="card-header bg-white">
            <i class="bi bi-calendar-check me-2"></i>
            {{ $appointments->total() }} Appointment{{ $appointments->total() != 1 ? 's' : '' }} Found
        </div>

        @if($appointments->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Appointments Found</h5>
                <p class="text-muted">Try adjusting your filters or check back later.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Complexity</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        <tr>
                            <td>
                                <code class="bg-light text-dark px-2 py-1 rounded">
                                    {{ $appointment->reference_number }}
                                </code>
                            </td>
                            <td>
                                <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                @if($appointment->clientRecord->phone)
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone"></i> {{ $appointment->clientRecord->phone }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $appointment->start_datetime->format('M d, Y') }}</div>
                                <small class="text-muted">
                                    {{ $appointment->start_datetime->format('g:i A') }} - 
                                    {{ $appointment->end_datetime->format('g:i A') }}
                                </small>
                            </td>
                            <td>
                                {{ $appointment->estimated_duration_minutes ?? 30 }} mins
                            </td>
                            <td>
                                @if($appointment->complexity_level)
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
                                    @endswitch
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </td>                            <td>
                                <a href="{{ route('lawyer.appointments.show', $appointment) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($appointment->status === 'pending')
                                    <form action="{{ route('lawyer.appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Decline"
                                            data-bs-toggle="modal" data-bs-target="#declineModal{{ $appointment->id }}">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
            <div class="card-footer bg-white">
                {{ $appointments->links() }}
            </div>
            @endif        @endif
    </div>
</div>

<!-- Decline Modals -->
@foreach($appointments as $appointment)
    @if($appointment->status === 'pending')
    <div class="modal fade" id="declineModal{{ $appointment->id }}" tabindex="-1">
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
                            You are about to decline the appointment for <strong>{{ $appointment->clientRecord->full_name }}</strong> 
                            scheduled on <strong>{{ $appointment->start_datetime->format('M d, Y g:i A') }}</strong>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Declining <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="decline_reason" rows="3" required
                                      placeholder="Please provide a reason for declining..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
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
