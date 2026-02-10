@extends('layouts.admin')

@section('title', "Today's Queue")
@section('page-title', "Today's Queue - " . now()->format('F j, Y'))

@section('content')
<div class="row g-4">
    <!-- Queue Stats -->
    <div class="col-12">
        <div class="row g-3">
            @php
                $waiting = $appointments->whereNull('started_at')->whereNotNull('checked_in_at')->count();
                $inProgress = $appointments->whereNotNull('started_at')->whereNull('completed_at')->count();
                $completed = $appointments->whereNotNull('completed_at')->count();
                $pending = $appointments->whereNull('checked_in_at')->count();
            @endphp
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-value text-warning">{{ $waiting }}</div>
                            <div class="stat-label">Waiting</div>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-value text-primary">{{ $inProgress }}</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-value text-success">{{ $completed }}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-value text-secondary">{{ $pending }}</div>
                            <div class="stat-label">Not Checked In</div>
                        </div>
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-person-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Queue List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Appointment Queue</span>
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Queue</th>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Lawyer</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                <tr class="{{ $apt->status === 'in_progress' ? 'table-primary' : '' }}">
                                    <td>
                                        @if($apt->queue_number)
                                            <span class="badge bg-dark fs-6">#{{ $apt->queue_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $apt->start_datetime->format('g:i A') }}</strong>
                                        <br><small class="text-muted">{{ $apt->estimated_duration_minutes }}m</small>
                                    </td>
                                    <td>
                                        <strong>{{ $apt->clientRecord->full_name }}</strong>
                                        <br><small class="text-muted">{{ $apt->reference_number }}</small>
                                    </td>
                                    <td>{{ $apt->lawyer->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $apt->status_color }}">
                                            {{ $apt->status_label }}
                                        </span>
                                        @if($apt->checked_in_at && !$apt->started_at)
                                            <br><small class="text-muted">
                                                Checked in {{ $apt->checked_in_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($apt->status === 'confirmed' && !$apt->checked_in_at)
                                            <form action="{{ route('admin.appointments.checkIn', $apt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="bi bi-person-check"></i> Check In
                                                </button>
                                            </form>
                                        @elseif($apt->checked_in_at && !$apt->started_at)
                                            <form action="{{ route('admin.appointments.start', $apt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-play-fill"></i> Start
                                                </button>
                                            </form>
                                        @elseif($apt->status === 'in_progress')
                                            <form action="{{ route('admin.appointments.complete', $apt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-lg"></i> Complete
                                                </button>
                                            </form>
                                        @elseif($apt->status === 'pending')
                                            <form action="{{ route('admin.appointments.confirm', $apt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check"></i> Confirm
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x display-4"></i>
                                        <p class="mt-2">No appointments for today</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Now Serving -->
    <div class="col-lg-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body text-center py-5">
                <h5 class="text-white-50 mb-3">NOW SERVING</h5>
                @php
                    $currentlyServing = $appointments->where('status', 'in_progress')->first();
                @endphp
                @if($currentlyServing)
                    <div class="display-1 fw-bold mb-3">
                        #{{ $currentlyServing->queue_number ?? '-' }}
                    </div>
                    <p class="mb-1">{{ $currentlyServing->clientRecord->full_name }}</p>
                    <p class="mb-0 text-white-50">{{ $currentlyServing->lawyer->user->name ?? 'N/A' }}</p>
                @else
                    <div class="display-4 mb-3">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                    <p class="mb-0">No active consultation</p>
                @endif
            </div>
        </div>
        
        <!-- Next Up -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-arrow-right-circle me-2"></i>Next Up
            </div>
            <div class="card-body p-0">
                @php
                    $nextUp = $appointments->whereNotNull('checked_in_at')
                        ->whereNull('started_at')
                        ->sortBy('queue_number')
                        ->take(3);
                @endphp
                @if($nextUp->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($nextUp as $apt)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-dark me-2">#{{ $apt->queue_number }}</span>
                                    {{ $apt->clientRecord->full_name }}
                                </div>
                                <small class="text-muted">{{ $apt->start_datetime->format('g:i A') }}</small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-muted">
                        <p class="mb-0">No one waiting</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Appointments -->
@if($upcomingAppointments->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-calendar-range me-2 text-info"></i>
                    <strong>Upcoming Appointments ({{ $upcomingAppointments->count() }})</strong>
                </span>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-primary">
                    View All Appointments
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Lawyer</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingAppointments as $apt)
                                <tr>
                                    <td>
                                        <strong>{{ $apt->start_datetime->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $apt->start_datetime->format('l') }}</small>
                                        @if($apt->start_datetime->isTomorrow())
                                            <br><span class="badge bg-info">Tomorrow</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $apt->start_datetime->format('g:i A') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $apt->estimated_duration_minutes ?? 30 }}m</small>
                                    </td>
                                    <td>
                                        <strong>{{ $apt->clientRecord->full_name }}</strong>
                                        <br><small class="text-muted">{{ $apt->reference_number }}</small>
                                    </td>
                                    <td>{{ $apt->lawyer->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $apt->status_color }}">
                                            {{ $apt->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($apt->status === 'pending')
                                            <form action="{{ route('admin.appointments.confirm', $apt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                                                    <i class="bi bi-check-lg"></i> Confirm
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endpush
@endsection
