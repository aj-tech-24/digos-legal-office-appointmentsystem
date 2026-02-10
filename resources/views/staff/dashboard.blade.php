@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Staff Dashboard</h1>
            <p class="text-muted mb-0">Today's appointments and queue management</p>
        </div>
        <div>
            <a href="{{ route('staff.queue') }}" class="btn btn-primary">
                <i class="bi bi-people me-2"></i>Open Queue
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg mb-3 mb-lg-0">
            <div class="card stat-card purple h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Total</h6>
                            <h2 class="mb-0">{{ $stats['today_total'] }}</h2>
                        </div>
                        <div class="text-purple opacity-50">
                            <i class="bi bi-calendar-day fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg mb-3 mb-lg-0">
            <div class="card stat-card primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Upcoming</h6>
                            <h2 class="mb-0">{{ $stats['upcoming_total'] }}</h2>
                        </div>
                        <div class="text-primary opacity-50">
                            <i class="bi bi-calendar-range fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg mb-3 mb-lg-0">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Checked In</h6>
                            <h2 class="mb-0">{{ $stats['checked_in'] }}</h2>
                        </div>
                        <div class="text-success opacity-50">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg mb-3 mb-lg-0">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Waiting</h6>
                            <h2 class="mb-0">{{ $stats['waiting'] }}</h2>
                        </div>
                        <div class="text-warning opacity-50">
                            <i class="bi bi-hourglass-split fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Confirmation</h6>
                            <h2 class="mb-0">{{ $stats['pending_confirmation'] }}</h2>
                        </div>
                        <div class="text-info opacity-50">
                            <i class="bi bi-clock fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-check me-2"></i>
                Today's Appointments
                <span class="badge bg-primary ms-2">{{ now()->format('l, F j, Y') }}</span>
            </h5>
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>

        @if($todaysAppointments->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Appointments Today</h5>
                <p class="text-muted mb-0">There are no scheduled appointments for today.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Queue</th>
                            <th>Time</th>
                            <th>Client</th>
                            <th>Lawyer</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todaysAppointments as $appointment)
                        <tr>
                            <td>
                                @if($appointment->queue_number)
                                    <span class="badge bg-primary fs-6">#{{ $appointment->queue_number }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $appointment->start_datetime->format('g:i A') }}</strong>
                                <br>
                                <small class="text-muted">{{ $appointment->estimated_duration_minutes ?? 30 }} mins</small>
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
                                {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                                @if($appointment->checked_in_at)
                                    <br>
                                    <small class="text-success">
                                        <i class="bi bi-check"></i> Checked in {{ $appointment->checked_in_at->format('g:i A') }}
                                    </small>
                                @endif
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
        @endif
    </div>

    <!-- Upcoming Appointments (Today + Future) -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range me-2"></i>
                Upcoming Appointments
                <span class="badge bg-info ms-2">{{ $upcomingAppointments->count() }} total</span>
            </h5>
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>

        @if($upcomingAppointments->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Upcoming Appointments</h5>
                <p class="text-muted mb-0">There are no upcoming appointments scheduled.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Client</th>
                            <th>Lawyer</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingAppointments as $appointment)
                        <tr>
                            <td>
                                <strong>{{ $appointment->start_datetime->format('M d, Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $appointment->start_datetime->format('l') }}</small>
                                @if($appointment->start_datetime->isToday())
                                    <br><span class="badge bg-success">Today</span>
                                @elseif($appointment->start_datetime->isTomorrow())
                                    <br><span class="badge bg-info">Tomorrow</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $appointment->start_datetime->format('g:i A') }}</strong>
                                <br>
                                <small class="text-muted">{{ $appointment->estimated_duration_minutes ?? 30 }} mins</small>
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
                                {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                                @if($appointment->checked_in_at)
                                    <br>
                                    <small class="text-success">
                                        <i class="bi bi-check"></i> Checked in {{ $appointment->checked_in_at->format('g:i A') }}
                                    </small>
                                @endif
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
        @endif
    </div>
</div>
@endsection
