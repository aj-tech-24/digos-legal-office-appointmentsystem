@extends('layouts.lawyer')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Welcome, {{ $lawyer->user->name }}!</h1>
            <p class="text-muted mb-0">Here's your schedule overview for today.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border p-2">
                <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-primary text-xs mb-1">Today's Appointments</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $stats['today_count'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-event text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-warning text-xs mb-1">Pending Requests</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $stats['pending_count'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-hourglass-split text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-danger text-xs mb-1">Upcoming Leaves/Hearings</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $stats['upcoming_unavailabilities_count'] }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-x text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 border-start border-4 border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-success text-xs mb-1">Completed (Month)</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $stats['completed_this_month'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-check-circle text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>Today's Schedule
                    </h6>
                    <span class="badge bg-primary rounded-pill">{{ $todaysAppointments->count() }} Appointments</span>
                </div>
                
                <div class="card-body p-0">
                    @if($todaysAppointments->isEmpty())
                        <div class="text-center py-5 px-4">
                            <div class="mb-3">
                                <i class="bi bi-cup-hot text-muted opacity-25" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted fw-bold">No Appointments Today</h5>
                            <p class="text-muted small mb-0">Your schedule is clear. Enjoy your day!</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($todaysAppointments as $appointment)
                            <a href="{{ route('lawyer.appointments.show', $appointment) }}" class="list-group-item list-group-item-action p-3 border-start border-4 border-primary">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-center bg-light rounded p-2 border">
                                            <div class="small fw-bold text-uppercase text-muted">{{ $appointment->start_datetime->format('M') }}</div>
                                            <div class="h5 mb-0 fw-bold">{{ $appointment->start_datetime->format('d') }}</div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">{{ $appointment->clientRecord->full_name }}</h6>
                                            <p class="mb-0 text-muted small">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $appointment->start_datetime->format('g:i A') }} - 
                                                {{ $appointment->start_datetime->addMinutes($appointment->estimated_duration_minutes ?? 30)->format('g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $appointment->status == 'in_progress' ? 'success' : 'primary' }} rounded-pill">
                                            {{ $appointment->status == 'in_progress' ? 'In Progress' : 'Confirmed' }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="bi bi-calendar-x me-2"></i>Upcoming Leaves & Hearings
                    </h6>
                    <a href="{{ route('lawyer.schedule') }}" class="btn btn-sm btn-outline-danger">
                        Manage Schedule
                    </a>
                </div>

                <div class="card-body p-0">
                    @if($upcomingUnavailabilities->isEmpty())
                        <div class="text-center py-5 px-4">
                            <div class="mb-3">
                                <i class="bi bi-calendar-check text-success opacity-25" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-success fw-bold">Fully Available</h5>
                            <p class="text-muted small mb-0">You have no upcoming blocked dates or hearings.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($upcomingUnavailabilities as $unavailable)
                            <div class="list-group-item p-3 border-start border-4 {{ $unavailable->reason == 'Court Hearing' ? 'border-danger' : 'border-warning' }}">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-center bg-light rounded p-2 border" style="min-width: 60px;">
                                            <div class="small fw-bold text-uppercase text-danger">
                                                {{ \Carbon\Carbon::parse($unavailable->unavailable_date)->format('M') }}
                                            </div>
                                            <div class="h5 mb-0 fw-bold">
                                                {{ \Carbon\Carbon::parse($unavailable->unavailable_date)->format('d') }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">{{ $unavailable->reason }}</h6>
                                            <p class="mb-0 text-muted small">
                                                @if($unavailable->is_whole_day || $unavailable->start_time == null)
                                                    <span class="badge bg-danger">Whole Day</span>
                                                @else
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($unavailable->start_time)->format('g:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($unavailable->end_time)->format('g:i A') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted fw-bold d-block">
                                            {{ \Carbon\Carbon::parse($unavailable->unavailable_date)->format('l') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-uppercase text-muted fw-bold small mb-3">Quick Actions</h6>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('lawyer.appointments.index') }}" class="btn btn-white w-100 p-3 shadow-sm border d-flex align-items-center justify-content-center hover-shadow">
                <i class="bi bi-calendar-check text-primary fs-4 me-2"></i>
                <span class="fw-bold text-gray-700">All Appointments</span>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('lawyer.schedule') }}" class="btn btn-white w-100 p-3 shadow-sm border d-flex align-items-center justify-content-center hover-shadow">
                <i class="bi bi-clock text-success fs-4 me-2"></i>
                <span class="fw-bold text-gray-700">Update Schedule</span>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('lawyer.profile') }}" class="btn btn-white w-100 p-3 shadow-sm border d-flex align-items-center justify-content-center hover-shadow">
                <i class="bi bi-person-gear text-info fs-4 me-2"></i>
                <span class="fw-bold text-gray-700">Edit Profile</span>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('lawyer.appointments.index', ['status' => 'pending']) }}" class="btn btn-white w-100 p-3 shadow-sm border d-flex align-items-center justify-content-center hover-shadow">
                <i class="bi bi-hourglass-split text-warning fs-4 me-2"></i>
                <span class="fw-bold text-gray-700">Pending Requests</span>
            </a>
        </div>
    </div>
</div>

<style>
    /* Custom Hover Effect for Quick Actions */
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transition: all 0.3s ease;
    }
    .btn-white {
        background-color: #fff;
    }
    .text-xs {
        font-size: .7rem;
    }
</style>
@endsection