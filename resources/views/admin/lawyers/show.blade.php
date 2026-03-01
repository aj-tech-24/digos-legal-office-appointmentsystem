@extends('layouts.admin')

@section('title', 'Lawyer Details')
@section('page-title', 'Lawyer: ' . $lawyer->user->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        {{-- Profile Card --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-badge me-2"></i>Lawyer Profile</span>
                @php
                    $statusColors = [
                        'pending'   => 'warning',
                        'approved'  => 'success',
                        'suspended' => 'danger',
                        'rejected'  => 'secondary',
                    ];
                @endphp
                <span class="badge bg-{{ $statusColors[$lawyer->status] ?? 'secondary' }} fs-6">
                    {{ ucfirst($lawyer->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Personal Information</h6>
                        <p class="mb-1"><strong>{{ $lawyer->user->name }}</strong></p>
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $lawyer->user->email }}</p>
                        <p class="mb-0"><i class="bi bi-card-text me-2"></i>License: <code>{{ $lawyer->license_number }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Professional Details</h6>
                        <p class="mb-1"><i class="bi bi-briefcase me-2"></i>{{ $lawyer->years_of_experience }} years of experience</p>
                        @if($lawyer->languages)
                            <p class="mb-0">
                                <i class="bi bi-translate me-2"></i>
                                {{ is_array($lawyer->languages) ? implode(', ', $lawyer->languages) : $lawyer->languages }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Specializations --}}
                <div class="mb-4">
                    <h6 class="text-muted">Specializations</h6>
                    @forelse($lawyer->specializations as $spec)
                        <span class="badge bg-{{ $spec->pivot->is_primary ? 'primary' : 'secondary' }} me-1 mb-1">
                            {{ $spec->name }}
                            @if($spec->pivot->is_primary)
                                <i class="bi bi-star-fill ms-1"></i>
                            @endif
                        </span>
                    @empty
                        <p class="text-muted small">No specializations assigned.</p>
                    @endforelse
                </div>

                @if($lawyer->bio)
                    <h6 class="text-muted">Bio</h6>
                    <div class="bg-light p-3 rounded mb-4">{{ $lawyer->bio }}</div>
                @endif

                @if($lawyer->description)
                    <h6 class="text-muted">Description</h6>
                    <div class="bg-light p-3 rounded">{{ $lawyer->description }}</div>
                @endif
            </div>
        </div>

        {{-- Recent Appointments --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-calendar-check me-2"></i>Recent Appointments
            </div>
            <div class="card-body p-0">
                @if($lawyer->appointments->count() > 0)
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
                                @foreach($lawyer->appointments as $appointment)
                                    <tr>
                                        <td><code>{{ $appointment->reference_number }}</code></td>
                                        <td>{{ $appointment->clientRecord->full_name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->formatted_date ?? \Carbon\Carbon::parse($appointment->start_datetime)->format('M j, Y g:i A') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $appointment->status_color ?? 'secondary' }}">
                                                {{ $appointment->status_label ?? ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x fs-4 d-block mb-2"></i>
                        <p class="mb-0 small">No appointments found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Quick Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Quick Info
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Max Daily Appts</td>
                        <td class="text-end"><span class="badge bg-light text-dark">{{ $lawyer->max_daily_appointments }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Consultation Duration</td>
                        <td class="text-end"><span class="badge bg-light text-dark">{{ $lawyer->default_consultation_duration }} min</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Appointments</td>
                        <td class="text-end"><span class="badge bg-light text-dark">{{ $lawyer->appointments->count() }}</span></td>
                    </tr>
                    @if($lawyer->approved_at)
                        <tr>
                            <td class="text-muted">Approved At</td>
                            <td class="text-end">{{ \Carbon\Carbon::parse($lawyer->approved_at)->format('M j, Y') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Actions
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.lawyers.edit', $lawyer) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i>Edit Lawyer
                </a>

                @if($lawyer->status === 'pending')
                    <form action="{{ route('admin.lawyers.approve', $lawyer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i>Approve Lawyer
                        </button>
                    </form>
                    <form action="{{ route('admin.lawyers.reject', $lawyer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Reject Lawyer
                        </button>
                    </form>
                @elseif($lawyer->status === 'approved')
                    <form action="{{ route('admin.lawyers.suspend', $lawyer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-pause-circle me-1"></i>Suspend Lawyer
                        </button>
                    </form>
                @elseif($lawyer->status === 'suspended')
                    <form action="{{ route('admin.lawyers.unsuspend', $lawyer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-play-circle me-1"></i>Unsuspend Lawyer
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.lawyers.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Back to Lawyers
                </a>
            </div>
        </div>

        {{-- Schedule Summary --}}
        @if($lawyer->schedules && $lawyer->schedules->count() > 0)
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock me-2"></i>Schedule
                </div>
                <div class="card-body">
                    @php
                        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    @endphp
                    @foreach($lawyer->schedules->sortBy('day_of_week') as $schedule)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">{{ $days[$schedule->day_of_week - 1] ?? 'Day '.$schedule->day_of_week }}</span>
                            @if($schedule->is_available)
                                <span class="small text-success">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                    &ndash;
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                </span>
                            @else
                                <span class="badge bg-secondary small">Off</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection