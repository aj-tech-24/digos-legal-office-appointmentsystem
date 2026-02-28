@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-primary small mb-1">Today's Appointments</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['today_appointments'] }}</div>
                        </div>
                        <i class="bi bi-calendar-check fs-1 text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-warning small mb-1">Pending Approval</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['pending_appointments'] }}</div>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-success small mb-1">Active Lawyers</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['approved_lawyers'] }}</div>
                        </div>
                        <i class="bi bi-person-badge fs-1 text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase fw-bold text-info small mb-1">Total Clients</div>
                            <div class="h3 mb-0 fw-bold">{{ $stats['total_clients'] }}</div>
                        </div>
                        <i class="bi bi-people fs-1 text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calendar-event me-2"></i>Today's Schedule
                    </h6>
                    <span class="badge bg-primary rounded-pill">{{ $todaysAppointments->count() }} Today</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-4">Time</th>
                                    <th>Client</th>
                                    <th>Lawyer</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todaysAppointments as $apt)
                                    @php
                                        $statusColor = match($apt->status) {
                                            'pending' => 'warning',
                                            'confirmed' => 'primary',
                                            'ongoing' => 'info',
                                            'completed' => 'success',
                                            'cancelled', 'no_show' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $apt->start_datetime->format('g:i A') }}</div>
                                            <small class="text-muted">{{ $apt->estimated_duration_minutes }} mins</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle me-2 d-flex justify-content-center align-items-center fw-bold border" style="width:35px; height:35px;">
                                                    {{ substr($apt->clientRecord->first_name ?? 'C', 0, 1) }}{{ substr($apt->clientRecord->last_name ?? 'L', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark small">{{ $apt->clientRecord->full_name }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $apt->reference_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($apt->lawyer)
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2 text-primary"><i class="bi bi-person-circle"></i></div>
                                                    <span class="small">{{ $apt->lawyer->user->name }}</span>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border border-{{ $statusColor }} border-opacity-25 rounded-pill px-2">
                                                {{ ucfirst($apt->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.appointments.show', $apt->id) }}" class="btn btn-sm btn-light border">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No appointments scheduled for today.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-person-plus me-2"></i>Pending Lawyer Approvals
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($pendingLawyers) && $pendingLawyers->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($pendingLawyers as $lawyer)
                                <li class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $lawyer->user->name }}</div>
                                        <div class="small text-muted">Lic: {{ $lawyer->license_number }}</div>
                                    </div>
                                    <a href="{{ route('admin.lawyers.show', $lawyer->id) }}" class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-1 mb-2"></i>
                            <p class="mb-0">All lawyers approved.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-calendar-week me-2"></i>Upcoming Appointments
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date & Time</th>
                                    <th>Client Details</th>
                                    <th>Assigned Lawyer</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingAppointments as $apt)
                                    @php
                                        $statusColor = match($apt->status) {
                                            'confirmed' => 'info',
                                            'ongoing' => 'warning',
                                            'pending' => 'secondary',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark">{{ $apt->start_datetime->format('M d, Y') }}</span>
                                                <span class="text-muted small">{{ $apt->start_datetime->format('l @ g:i A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    {{ substr($apt->clientRecord->first_name ?? 'C', 0, 1) }}{{ substr($apt->clientRecord->last_name ?? 'L', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $apt->clientRecord->full_name }}</div>
                                                    <small class="text-muted">Ref: {{ $apt->reference_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($apt->lawyer)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light border me-2 d-flex justify-content-center align-items-center" style="width:30px; height:30px;">
                                                        <i class="bi bi-person text-secondary"></i>
                                                    </div>
                                                    <span>{{ $apt->lawyer->user->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Waiting Assignment</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} px-3 py-2">
                                                <i class="bi bi-circle-fill small me-1"></i> {{ ucfirst($apt->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.appointments.show', $apt->id) }}">
                                                            <i class="bi bi-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">No upcoming appointments found.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($upcomingAppointments->count() > 0)
                        <div class="card-footer bg-white text-center py-2">
                            <a href="{{ route('admin.appointments.index') }}" class="text-decoration-none small fw-bold">View All Appointments</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection