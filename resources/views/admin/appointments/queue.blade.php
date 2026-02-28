@extends('layouts.admin')

@section('title', "Today's Queue")

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">Live Queue Monitor</h1>
            <p class="text-muted mb-0"><i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-list-ul me-1"></i> All Records
            </a>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Board
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @php
            // Safe logic calculation
            $waiting = $appointments->whereNull('started_at')->whereNotNull('checked_in_at')->where('status', '!=', 'cancelled')->count();
            $inProgress = $appointments->whereNotNull('started_at')->whereNull('completed_at')->count();
            $completed = $appointments->where('status', 'completed')->count();
            // Not Arrived = Confirmed but NO check-in yet
            $pending = $appointments->whereNull('checked_in_at')->where('status', 'confirmed')->count();
        @endphp
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0 fw-bold text-warning">{{ $waiting }}</div>
                            <div class="text-muted small fw-bold text-uppercase">Waiting Room</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split h4 mb-0"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0 fw-bold text-primary">{{ $inProgress }}</div>
                            <div class="text-muted small fw-bold text-uppercase">In Session</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-person-workspace h4 mb-0"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0 fw-bold text-success">{{ $completed }}</div>
                            <div class="text-muted small fw-bold text-uppercase">Completed</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check-circle h4 mb-0"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0 fw-bold text-secondary">{{ $pending }}</div>
                            <div class="text-muted small fw-bold text-uppercase">Not Arrived</div>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-clock h4 mb-0"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-gray-800"><i class="bi bi-people me-2"></i>Today's Appointment Queue</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Queue #</th>
                                <th>Time</th>
                                <th>Client Name</th>
                                <th>Lawyer</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                @php
                                    $isOngoing = in_array($apt->status, ['ongoing', 'in_progress']);
                                    $isCompleted = $apt->status === 'completed';
                                    
                                    // Status Label Logic
                                    $statusLabel = 'Pending';
                                    $statusClass = 'bg-secondary';
                                    
                                    if($apt->status == 'confirmed') { $statusClass = 'bg-info text-dark bg-opacity-25'; $statusLabel = 'Confirmed'; }
                                    elseif($apt->status == 'checked_in') { $statusClass = 'bg-primary text-white'; $statusLabel = 'Checked In'; }
                                    elseif($isOngoing) { $statusClass = 'bg-primary text-white spinner-grow-sm'; $statusLabel = 'In Progress'; }
                                    elseif($isCompleted) { $statusClass = 'bg-success text-white'; $statusLabel = 'Completed'; }
                                    elseif($apt->status == 'cancelled') { $statusClass = 'bg-danger text-white'; $statusLabel = 'Cancelled'; }
                                @endphp
                                <tr class="{{ $isOngoing ? 'table-primary bg-opacity-10' : '' }}">
                                    <td class="ps-4">
                                        {{-- FIXED: Use loop iteration for queue number --}}
                                        <span class="badge bg-dark rounded-pill fs-6">#{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $apt->start_datetime->format('g:i A') }}</div>
                                        <small class="text-muted">{{ $apt->estimated_duration_minutes ?? 60 }}m</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $apt->clientRecord->full_name }}</span>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $apt->reference_number }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $apt->lawyer->user->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $statusClass }} px-3 py-2">
                                            @if($isOngoing) <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true"></span> @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            {{-- FIXED ACTION BUTTON LOGIC --}}
                                            
                                            @if($isCompleted)
                                                {{-- 1. Completed: View Only --}}
                                                <button class="btn btn-sm btn-light border" disabled>Done</button>

                                            @elseif($isOngoing)
                                                {{-- 2. In Progress: Finish --}}
                                                <form action="{{ route('admin.appointments.complete', $apt->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Complete Consultation">
                                                        <i class="bi bi-check-lg me-1"></i> Finish
                                                    </button>
                                                </form>

                                            @elseif($apt->status === 'checked_in' || ($apt->checked_in_at && !$apt->started_at))
                                                {{-- 3. Checked In: Start --}}
                                                <form action="{{ route('admin.appointments.start', $apt->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Start Consultation">
                                                        <i class="bi bi-play-fill me-1"></i> Start
                                                    </button>
                                                </form>

                                            @elseif($apt->status === 'confirmed' || $apt->status === 'pending')
                                                {{-- 4. Not Arrived: Check In --}}
                                                <form action="{{ route('admin.appointments.checkIn', $apt->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-info text-white" title="Check In Client">
                                                        <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('admin.appointments.show', $apt->id) }}" class="btn btn-sm btn-outline-secondary ms-1" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-calendar-x display-4 text-muted opacity-50"></i>
                                        <p class="mt-3 text-muted fw-bold">No appointments scheduled for today.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card bg-primary text-white shadow mb-4 border-0" style="background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);">
                <div class="card-body text-center py-5">
                    <h6 class="text-white-50 mb-3 text-uppercase fw-bold ls-2">Now Serving</h6>
                    @php
                        $currentlyServing = $appointments->filter(function($a) {
                            return in_array($a->status, ['ongoing', 'in_progress']) || ($a->started_at && !$a->completed_at);
                        })->first();
                    @endphp
                    
                    @if($currentlyServing)
                        <div class="display-1 fw-bold mb-2 animate__animated animate__pulse animate__infinite">
                            #{{ $appointments->search($currentlyServing) + 1 }}
                        </div>
                        <h3 class="mb-1 fw-bold">{{ $currentlyServing->clientRecord->full_name }}</h3>
                        <p class="mb-0 text-white-50 mt-2 fs-5">
                            <i class="bi bi-person-badge me-2"></i>
                            Atty. {{ $currentlyServing->lawyer->user->name ?? 'Lawyer' }}
                        </p>
                    @else
                        <div class="py-3">
                            <i class="bi bi-cup-hot display-3 text-white-50"></i>
                            <h4 class="mt-3">Waiting for next client...</h4>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-fast-forward-circle me-2"></i>Next Up in Lobby</h6>
                </div>
                <div class="list-group list-group-flush">
                    @php
                        // Filter: Checked In, Not Started, Not Cancelled. Sorted by Check-in time.
                        $nextUp = $appointments->whereNotNull('checked_in_at')
                                               ->whereNull('started_at')
                                               ->where('status', '!=', 'cancelled')
                                               ->sortBy('checked_in_at') 
                                               ->take(4);
                    @endphp

                    @forelse($nextUp as $apt)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-dark rounded-pill me-3 fs-6">#{{ $loop->iteration }}</span>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $apt->clientRecord->full_name }}</h6>
                                    {{-- FIXED: Correct Lawyer Name Access --}}
                                    <small class="text-muted">Atty. {{ $apt->lawyer->user->name ?? 'Unassigned' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border">{{ $apt->start_datetime->format('g:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-4 d-block mb-2"></i>
                            <small>No checked-in clients waiting.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh every 60 seconds
    setTimeout(function() {
        window.location.reload();
    }, 60000);
</script>
<style>
    .ls-1 { letter-spacing: 1px; }
    .ls-2 { letter-spacing: 2px; }
</style>
@endpush

@endsection