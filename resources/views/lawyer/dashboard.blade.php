@extends('layouts.lawyer')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="mb-4">
        <h1 class="h3 mb-1">Welcome, Atty. {{ $lawyer->user->name }}!</h1>
        <p class="text-muted mb-0">Here's an overview of your schedule and appointments.</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Today's Appointments</h6>
                            <h2 class="mb-0">{{ $stats['today_count'] }}</h2>
                        </div>
                        <div class="text-primary opacity-50">
                            <i class="bi bi-calendar-day fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending Confirmation</h6>
                            <h2 class="mb-0">{{ $stats['pending_count'] }}</h2>
                        </div>
                        <div class="text-warning opacity-50">
                            <i class="bi bi-hourglass-split fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Upcoming (7 days)</h6>
                            <h2 class="mb-0">{{ $stats['upcoming_count'] }}</h2>
                        </div>
                        <div class="text-info opacity-50">
                            <i class="bi bi-calendar-week fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed This Month</h6>
                            <h2 class="mb-0">{{ $stats['completed_this_month'] }}</h2>
                        </div>
                        <div class="text-success opacity-50">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-day text-primary me-2"></i>
                        Today's Schedule
                    </h5>
                    <span class="badge bg-primary rounded-pill">{{ $todaysAppointments->count() }}</span>
                </div>
                
                @if($todaysAppointments->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check display-4 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No Appointments Today</h5>
                        <p class="text-muted">Enjoy your day off!</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($todaysAppointments as $appointment)
                        <a href="{{ route('lawyer.appointments.show', $appointment) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-{{ $appointment->status == 'in_progress' ? 'primary' : 'info' }} me-2">
                                            {{ $appointment->start_datetime->format('g:i A') }}
                                        </span>
                                        <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $appointment->estimated_duration_minutes ?? 30 }} mins
                                        @if($appointment->complexity_level)
                                        <span class="badge bg-light text-dark ms-2">
                                            {{ ucfirst($appointment->complexity_level) }} complexity
                                        </span>
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $appointment->status_color }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week text-info me-2"></i>
                        Upcoming Appointments
                    </h5>
                    <a href="{{ route('lawyer.appointments.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                @if($upcomingAppointments->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No Upcoming Appointments</h5>
                        <p class="text-muted">No appointments scheduled for the next 7 days.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($upcomingAppointments as $appointment)
                        <a href="{{ route('lawyer.appointments.show', $appointment) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="mb-1">
                                        <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $appointment->start_datetime->format('M d, Y') }} at 
                                        {{ $appointment->start_datetime->format('g:i A') }}
                                    </small>
                                </div>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('lawyer.appointments.index') }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-calendar-check d-block fs-1 mb-2"></i>
                                View All Appointments
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('lawyer.schedule') }}" class="btn btn-outline-success btn-lg w-100">
                                <i class="bi bi-clock d-block fs-1 mb-2"></i>
                                Manage Schedule
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('lawyer.profile') }}" class="btn btn-outline-info btn-lg w-100">
                                <i class="bi bi-person-badge d-block fs-1 mb-2"></i>
                                Update Profile
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('lawyer.appointments.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-lg w-100">
                                <i class="bi bi-hourglass-split d-block fs-1 mb-2"></i>
                                Pending Appointments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
