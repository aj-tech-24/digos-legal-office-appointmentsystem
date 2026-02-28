@extends('layouts.admin')

@section('title', 'Manage Appointments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Appointments</h1>
            <p class="text-muted mb-0">View and manage all system appointments</p>
        </div>
        <div>
             <a href="{{ route('admin.appointments.queue') }}" class="btn btn-info text-white">
                <i class="bi bi-clock-history me-1"></i> View Today's Queue
            </a>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Ref # or Name">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Section --}}
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
                            <th>Lawyer</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>
                                    <code class="bg-light text-dark px-2 py-1 rounded">
                                        {{ $appointment->reference_number }}
                                    </code>
                                </td>
                                <td>
                                    <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                </td>
                                <td>
                                    @if($appointment->lawyer)
                                        {{ $appointment->lawyer->name }}
                                    @else
                                        <span class="text-muted fst-italic">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <x-status-badge :status="$appointment->status" />
                                </td>
                                <td>
                                    {{ $appointment->start_datetime->format('Y-m-d h:i A') }}
                                </td>
                                <td>
                                    @include('components.appointment-actions', ['appointment' => $appointment])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
            <div class="card-footer bg-white">
                {{ $appointments->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @endif
    </div>
</div>

{{-- MODALS SECTION --}}
@foreach($appointments as $appointment)
    @if($appointment->status === 'pending')
    
    {{-- Confirm Modal --}}
    <div class="modal fade" id="confirmModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST">
                    @csrf
                    
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Confirm Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            Confirming will send an email to <strong>{{ $appointment->clientRecord->first_name }}</strong>.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Requirements:</label>
                            <div class="card p-2 bg-light border">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Valid Government ID" checked>
                                    <label class="form-check-label">Valid Government ID</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="Relevant Documents" checked>
                                    <label class="form-check-label">Relevant Documents</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Decline Modal --}}
    <div class="modal fade" id="declineModal-{{ $appointment->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.appointments.decline', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Decline Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to decline <strong>{{ $appointment->clientRecord->full_name }}</strong>?</p>
                        <textarea class="form-control" name="decline_reason" rows="3" required placeholder="Reason for declining..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Decline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection