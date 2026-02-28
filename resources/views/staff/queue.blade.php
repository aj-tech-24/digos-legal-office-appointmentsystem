@extends('layouts.staff')

@section('title', 'Queue Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">Queue Management</h2>
            <p class="text-muted small">Manage client flow for {{ now()->format('F j, Y') }}</p>
        </div>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom border-primary border-3">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock me-2"></i>Waiting for Arrival
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($waitingToCheckIn as $apt)
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $apt->clientRecord->full_name }}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($apt->start_datetime)->format('g:i A') }}
                                            <span class="mx-1">•</span> {{ $apt->lawyer->user->name ?? 'Unassigned' }}
                                        </div>
                                    </div>
                                    <form action="{{ route('staff.appointments.checkIn', $apt->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                            Check In <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-check-all fs-1 mb-2"></i>
                                <p>No expected clients pending arrival.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom border-success border-3">
                    <h5 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-list-ol me-2"></i>Active Queue
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Queue #</th>
                                    <th>Client</th>
                                    <th>Lawyer</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $apt)
                                    <tr class="{{ $apt->status === 'in_progress' ? 'table-success' : '' }}">
                                        <td class="ps-4">
                                            <span class="badge bg-dark fs-6 rounded-pill">#{{ $apt->queue_number }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $apt->clientRecord->full_name }}</div>
                                            <small class="text-muted">Arrived: {{ \Carbon\Carbon::parse($apt->checked_in_at)->format('g:i A') }}</small>
                                        </td>
                                        <td>{{ $apt->lawyer->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($apt->status === 'in_progress')
                                                <span class="badge bg-success bg-opacity-25 text-success animate-pulse">In Session</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Waiting</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('staff.appointments.show', $apt->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 mb-2"></i>
                                            <p>Queue is currently empty.</p>
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
</div>
@endsection