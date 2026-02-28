@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">Staff Dashboard</h1>
            <p class="text-muted small mb-0">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div>
            <a href="{{ route('staff.queue') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-list-ol me-2"></i>Manage Queue
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-muted small mb-1">Total Appointments</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['today_total'] }}</div>
                        </div>
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-calendar-event fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-warning small mb-1">Pending Requests</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['pending_requests'] }}</div>
                        </div>
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-info small mb-1">Waiting Check-in</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['waiting_checkin'] }}</div>
                        </div>
                        <div class="icon-circle bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-success small mb-1">In Session</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['in_session'] }}</div>
                        </div>
                        <div class="icon-circle bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-broadcast fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-table me-2"></i>Today's Schedule
            </h6>
            
            <form action="{{ route('staff.dashboard') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search name...">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 140px;">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Session</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Time</th>
                            <th>Client Info</th>
                            <th>Assigned Lawyer</th>
                            <th>Status</th>
                            <th>Queue</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaysAppointments as $apt)
                            @php
                                $statusColor = match($apt->status) {
                                    'pending' => 'warning',
                                    'confirmed' => 'primary',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($apt->start_datetime)->format('g:i A') }}</div>
                                    <small class="text-muted">{{ $apt->estimated_duration_minutes ?? 30 }} mins</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $apt->clientRecord->full_name }}</div>
                                    <small class="text-muted">{{ $apt->reference_number }}</small>
                                </td>
                                <td>
                                    @if($apt->lawyer && $apt->lawyer->user)
                                        <span class="d-inline-flex align-items-center">
                                            <i class="bi bi-person-badge me-1 text-muted"></i> {{ $apt->lawyer->user->name }}
                                        </span>
                                    @else
                                        <span class="text-danger small fst-italic"><i class="bi bi-exclamation-circle"></i> Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $apt->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($apt->queue_number)
                                        <span class="badge bg-dark rounded-pill shadow-sm">#{{ $apt->queue_number }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('staff.appointments.show', $apt->id) }}">
                                                    <i class="bi bi-eye me-2 text-secondary"></i>View Details
                                                </a>
                                            </li>

                                            {{-- DYNAMIC ACTIONS BASED ON STATUS --}}
                                            @if($apt->status === 'pending')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('staff.appointments.confirm', $apt->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="bi bi-check-circle me-2"></i>Confirm Appointment
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif

                                            @if($apt->status === 'confirmed' && !$apt->checked_in_at)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('staff.appointments.checkIn', $apt->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-primary fw-bold">
                                                            <i class="bi bi-box-arrow-in-right me-2"></i>Check In Client
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-calendar-x display-4"></i></div>
                                    <p class="h6 text-muted">No appointments found.</p>
                                    @if(request('status') || request('search'))
                                        <a href="{{ route('staff.dashboard') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection