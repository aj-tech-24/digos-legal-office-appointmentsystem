@extends('layouts.admin')

@section('title', 'Client Records')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Client Records</h1>
            <p class="text-muted mb-0">Manage client profiles and client history</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Name, email, phone, or reference...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                    <a href="{{ route('admin.clients.summary', request()->all()) }}" target="_blank" class="btn btn-secondary" title="Print Summary / Save as PDF">
                        <i class="bi bi-printer"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-people me-2"></i>
                {{ $clients->total() }} Client{{ $clients->total() != 1 ? 's' : '' }} Found
            </span>
        </div>

        @if($clients->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-people display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Client Records Found</h5>
                <p class="text-muted">Client records are created when appointments are booked.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Reference</th>
                            <th>Appointments</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $client->full_name }}</strong>
                                </div>
                                @if($client->address)
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ Str::limit($client->address, 30) }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($client->email)
                                    <div><i class="bi bi-envelope text-muted me-1"></i> {{ $client->email }}</div>
                                @endif
                                @if($client->phone)
                                    <div><i class="bi bi-telephone text-muted me-1"></i> {{ $client->phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($client->reference_number)
                                    <code class="bg-light text-dark px-2 py-1 rounded">{{ $client->reference_number }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $client->appointments_count }}
                                </span>
                            </td>
                            <td>
                                @switch($client->status)
                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                        @break
                                    @case('closed')
                                        <span class="badge bg-secondary">Closed</span>
                                        @break
                                    @case('archived')
                                        <span class="badge bg-dark">Archived</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $client->created_at->format('M d, Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.clients.show', $client) }}" 
                                       class="btn btn-outline-primary" 
                                       title="View Record">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
            <div class="card-footer bg-white">
                {{ $clients->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
