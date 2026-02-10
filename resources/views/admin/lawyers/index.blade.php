@extends('layouts.admin')

@section('title', 'Lawyers')
@section('page-title', 'Manage Lawyers')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.lawyers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, license..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Specialization</label>
                <select name="specialization" class="form-select">
                    <option value="">All Specializations</option>
                    @foreach($specializations as $spec)
                        <option value="{{ $spec->id }}" {{ request('specialization') == $spec->id ? 'selected' : '' }}>
                            {{ $spec->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.lawyers.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end">
                <a href="{{ route('admin.lawyers.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i>Add Lawyer
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lawyers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lawyer</th>
                        <th>License #</th>
                        <th>Specializations</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Appointments</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lawyers as $lawyer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-person-badge text-primary"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $lawyer->user->name }}</strong>
                                        <br><small class="text-muted">{{ $lawyer->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $lawyer->license_number }}</code></td>
                            <td>
                                @foreach($lawyer->specializations as $spec)
                                    <span class="badge bg-{{ $spec->pivot->is_primary ? 'primary' : 'secondary' }} me-1">
                                        {{ $spec->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td>{{ $lawyer->years_of_experience }} years</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'suspended' => 'danger',
                                        'rejected' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$lawyer->status] ?? 'secondary' }}">
                                    {{ ucfirst($lawyer->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $lawyer->appointments_count ?? $lawyer->appointments->count() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    @if($lawyer->status === 'pending')
                                        <form action="{{ route('admin.lawyers.approve', $lawyer) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.lawyers.reject', $lawyer) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.lawyers.show', $lawyer) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.lawyers.edit', $lawyer) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x display-4"></i>
                                <p class="mt-2">No lawyers found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lawyers->hasPages())
        <div class="card-footer">
            {{ $lawyers->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
