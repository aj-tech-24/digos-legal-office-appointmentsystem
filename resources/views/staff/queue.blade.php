@extends('layouts.staff')

@section('title', 'Queue Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Queue Management</h1>
            <p class="text-muted mb-0">Today's check-in queue - {{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Waiting to Check In -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-clock me-2"></i>
                        Waiting to Check In ({{ $waitingToCheckIn->count() }})
                    </h5>
                </div>
                
                @if($waitingToCheckIn->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle display-4 text-success mb-3 d-block"></i>
                        <h6 class="text-muted">No clients waiting to check in today</h6>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($waitingToCheckIn as $appointment)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        Scheduled: {{ $appointment->start_datetime->format('g:i A') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        Atty. {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
                                    </small>
                                </div>
                                <form action="{{ route('staff.appointments.checkIn', $appointment) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Queue -->
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Active Queue ({{ $appointments->count() }})
                    </h5>
                </div>
                
                @if($appointments->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                        <h6 class="text-muted">No clients in queue</h6>
                        <p class="text-muted small">Clients will appear here after checking in</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Queue #</th>
                                    <th>Client</th>
                                    <th>Lawyer</th>
                                    <th>Checked In</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appointment)
                                <tr class="{{ $appointment->status === 'in_progress' ? 'table-primary' : '' }}">
                                    <td>
                                        <span class="badge bg-primary fs-5">#{{ $appointment->queue_number }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->clientRecord->full_name }}</strong>
                                        @if($appointment->clientRecord->phone)
                                            <br>
                                            <small class="text-muted">{{ $appointment->clientRecord->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        Atty. {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
                                    </td>
                                    <td>
                                        {{ $appointment->checked_in_at->format('g:i A') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $appointment->checked_in_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($appointment->status === 'in_progress')
                                            <span class="badge bg-primary">
                                                <i class="bi bi-play-fill me-1"></i> In Progress
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-hourglass me-1"></i> Waiting
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    @if($upcomingAppointments->count() > 0)
    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range me-2"></i>
                Upcoming Appointments ({{ $upcomingAppointments->count() }})
            </h5>
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-light">
                View All
            </a>
        </div>
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
                            @if($appointment->start_datetime->isTomorrow())
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
                            Atty. {{ $appointment->lawyer->user->name ?? 'Not assigned' }}
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
                                
                                <a href="{{ route('staff.appointments.show', $appointment) }}" 
                                   class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endpush
@endsection
