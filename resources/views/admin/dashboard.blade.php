@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['today_appointments'] }}</div>
                    <div class="stat-label">Today's Appointments</div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['pending_appointments'] }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['approved_lawyers'] }}</div>
                    <div class="stat-label">Active Lawyers</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['total_clients'] }}</div>
                    <div class="stat-label">Total Clients</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Appointments -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Today's Appointments</span>
                <a href="{{ route('admin.appointments.queue') }}" class="btn btn-sm btn-outline-primary">View Queue</a>
            </div>
            <div class="card-body p-0">
                @if($todayAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Client</th>
                                    <th>Lawyer</th>
                                    <th>Status</th>
                                    <th>Queue</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayAppointments as $apt)
                                    <tr>
                                        <td>
                                            <strong>{{ $apt->start_datetime->format('g:i A') }}</strong>
                                            <br><small class="text-muted">{{ $apt->estimated_duration_minutes }} mins</small>
                                        </td>
                                        <td>
                                            {{ $apt->clientRecord->full_name }}
                                            <br><small class="text-muted">{{ $apt->reference_number }}</small>
                                        </td>
                                        <td>{{ $apt->lawyer->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $apt->status_color }} badge-status">
                                                {{ $apt->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($apt->queue_number)
                                                <span class="badge bg-dark">#{{ $apt->queue_number }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x display-4"></i>
                        <p class="mt-2">No appointments scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Pending Lawyers -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-plus me-2"></i>Pending Lawyers</span>
                <span class="badge bg-warning">{{ $stats['pending_lawyers'] }}</span>
            </div>
            <div class="card-body p-0">
                @if($pendingLawyers->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($pendingLawyers as $lawyer)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $lawyer->user->name }}</strong>
                                    <br><small class="text-muted">{{ $lawyer->license_number }}</small>
                                </div>
                                <div>
                                    <form action="{{ route('admin.lawyers.approve', $lawyer) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.lawyers.show', $lawyer) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle display-4"></i>
                        <p class="mt-2 mb-0">No pending approvals</p>
                    </div>
                @endif
            </div>
            @if($stats['pending_lawyers'] > 5)
                <div class="card-footer text-center">
                    <a href="{{ route('admin.lawyers.index', ['status' => 'pending']) }}">View all pending</a>
                </div>
            @endif
        </div>
        
        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Overview
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Appointments</span>
                    <strong>{{ $stats['total_appointments'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Lawyers</span>
                    <strong>{{ $stats['total_lawyers'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Pending Lawyers</span>
                    <strong>{{ $stats['pending_lawyers'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">System Users</span>
                    <strong>{{ $stats['total_users'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Appointments -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Recent Appointments</span>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Client</th>
                        <th>Lawyer</th>
                        <th>Date & Time</th>
                        <th>Complexity</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAppointments as $apt)
                        <tr>
                            <td><code>{{ $apt->reference_number }}</code></td>
                            <td>
                                {{ $apt->clientRecord->full_name }}
                                <br><small class="text-muted">{{ $apt->clientRecord->email }}</small>
                            </td>
                            <td>{{ $apt->lawyer->user->name ?? 'N/A' }}</td>
                            <td>
                                {{ $apt->start_datetime->format('M j, Y') }}
                                <br><small class="text-muted">{{ $apt->formatted_time_range }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $apt->complexity_level === 'complex' ? 'danger' : ($apt->complexity_level === 'moderate' ? 'warning' : 'success') }}">
                                    {{ ucfirst($apt->complexity_level) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $apt->status_color }} badge-status">
                                    {{ $apt->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No appointments yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
