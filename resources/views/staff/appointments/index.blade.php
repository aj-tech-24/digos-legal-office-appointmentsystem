@extends('layouts.staff')

@section('title', 'Appointments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Appointments</h1>
            <p class="text-muted mb-0">View and manage appointments</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.appointments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                    <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
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
            {{ $appointments->total() }} Appointment{{ $appointments->total() != 1 ? 's' : '' }}
        </div>

        @if($appointments->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Appointments Found</h5>
                <p class="text-muted">Try adjusting your filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Date & Time</th>
                            <th>Lawyer</th>
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
                                {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($appointment->status === 'pending')
                                        <form action="{{ route('staff.appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Confirm">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($appointment->status === 'confirmed' && !$appointment->checked_in_at)
                                        <form action="{{ route('staff.appointments.checkIn', $appointment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" title="Check In">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('staff.appointments.show', $appointment) }}" 
                                       class="btn btn-outline-secondary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
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
            @endif
        @endif
    </div>
</div>
@endsection
