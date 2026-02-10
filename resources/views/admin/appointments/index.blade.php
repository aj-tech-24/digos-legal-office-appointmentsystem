@extends('layouts.admin')

@section('title', 'Appointments')
@section('page-title', 'Manage Appointments')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Reference, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Lawyer</label>
                <select name="lawyer_id" class="form-select">
                    <option value="">All Lawyers</option>
                    @foreach($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}" {{ request('lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                            {{ $lawyer->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary me-2">Reset</a>
                <a href="{{ route('admin.appointments.summary', request()->all()) }}" target="_blank" class="btn btn-secondary" title="Print Summary / Save as PDF">
                    <i class="bi bi-printer"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table -->
<div class="card">
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
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr>
                            <td><code>{{ $apt->reference_number }}</code></td>
                            <td>
                                <a href="{{ route('admin.clients.show', $apt->clientRecord) }}">
                                    {{ $apt->clientRecord->full_name }}
                                </a>
                                <br><small class="text-muted">{{ $apt->clientRecord->email }}</small>
                            </td>
                            <td>
                                @if($apt->lawyer)
                                    <a href="{{ route('admin.lawyers.show', $apt->lawyer) }}">
                                        {{ $apt->lawyer->user->name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                {{ $apt->start_datetime->format('M j, Y') }}
                                <br><small class="text-muted">{{ $apt->formatted_time_range }}</small>
                            </td>
                            <td>
                                @php
                                    $complexityColors = ['simple' => 'success', 'moderate' => 'warning', 'complex' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $complexityColors[$apt->complexity_level] ?? 'secondary' }}">
                                    {{ ucfirst($apt->complexity_level) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $apt->status_color }}">
                                    {{ $apt->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    @if($apt->status === 'pending')
                                        <form action="{{ route('admin.appointments.confirm', $apt) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x display-4"></i>
                                <p class="mt-2">No appointments found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($appointments->hasPages())
        <div class="card-footer">
            {{ $appointments->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
