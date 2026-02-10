@extends('layouts.admin')

@section('title', 'View Lawyer')
@section('page-title', $lawyer->user->name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                    <i class="bi bi-person-badge display-4 text-primary"></i>
                </div>
                <h4>{{ $lawyer->user->name }}</h4>
                <p class="text-muted mb-2">{{ $lawyer->user->email }}</p>
                <p class="mb-3">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'suspended' => 'danger',
                            'rejected' => 'secondary',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$lawyer->status] ?? 'secondary' }} fs-6">
                        {{ ucfirst($lawyer->status) }}
                    </span>
                </p>
                
                <div class="d-flex justify-content-center gap-2">
                    @if($lawyer->status === 'pending')
                        <form action="{{ route('admin.lawyers.approve', $lawyer) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>Approve
                            </button>
                        </form>
                    @elseif($lawyer->status === 'approved')
                        <form action="{{ route('admin.lawyers.suspend', $lawyer) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-pause-circle me-1"></i>Suspend
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.lawyers.edit', $lawyer) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Details
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">License Number</td>
                        <td class="text-end"><code>{{ $lawyer->license_number }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Experience</td>
                        <td class="text-end">{{ $lawyer->years_of_experience }} years</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Languages</td>
                        <td class="text-end">{{ implode(', ', $lawyer->languages ?? []) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Max Daily Appointments</td>
                        <td class="text-end">{{ $lawyer->max_daily_appointments }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Consultation Duration</td>
                        <td class="text-end">{{ $lawyer->default_consultation_duration }} mins</td>
                    </tr>
                    @if($lawyer->approved_at)
                        <tr>
                            <td class="text-muted">Approved At</td>
                            <td class="text-end">{{ $lawyer->approved_at->format('M j, Y') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        
        <!-- Specializations -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-tags me-2"></i>Specializations
            </div>
            <div class="card-body">
                @foreach($lawyer->specializations as $spec)
                    <span class="badge bg-{{ $spec->pivot->is_primary ? 'primary' : 'secondary' }} me-1 mb-1">
                        {{ $spec->name }}
                        @if($spec->pivot->is_primary)
                            <i class="bi bi-star-fill ms-1"></i>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
        
        <!-- Schedule -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock me-2"></i>Weekly Schedule
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @php
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        @endphp
                        @foreach($days as $index => $day)
                            @php
                                $schedule = $lawyer->schedules->where('day_of_week', $index)->first();
                            @endphp
                            <tr>
                                <td>{{ $day }}</td>
                                <td class="text-end">
                                    @if($schedule && $schedule->is_available)
                                        <span class="text-success">
                                            {{ date('g:i A', strtotime($schedule->start_time)) }} - 
                                            {{ date('g:i A', strtotime($schedule->end_time)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Bio -->
        @if($lawyer->bio || $lawyer->description)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-lines-fill me-2"></i>About
                </div>
                <div class="card-body">
                    @if($lawyer->bio)
                        <p>{{ $lawyer->bio }}</p>
                    @endif
                    @if($lawyer->description)
                        <hr>
                        <p class="mb-0">{{ $lawyer->description }}</p>
                    @endif
                </div>
            </div>
        @endif
        
        <!-- Recent Appointments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Recent Appointments</span>
                <a href="{{ route('admin.appointments.index', ['lawyer_id' => $lawyer->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Client</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lawyer->appointments as $apt)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.appointments.show', $apt) }}">
                                            <code>{{ $apt->reference_number }}</code>
                                        </a>
                                    </td>
                                    <td>{{ $apt->clientRecord->full_name }}</td>
                                    <td>
                                        {{ $apt->start_datetime->format('M j, Y') }}
                                        <br><small class="text-muted">{{ $apt->formatted_time_range }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $apt->status_color }}">
                                            {{ $apt->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No appointments yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
