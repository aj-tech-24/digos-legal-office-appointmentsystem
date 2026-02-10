@extends('layouts.admin')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
<!-- Filters & Add -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.staff.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>Search
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-3 d-flex align-items-end justify-content-end">
                <a href="{{ route('admin.staff.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i>Add Staff
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Staff Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-people me-2"></i>
            {{ $staffUsers->total() }} Staff Member{{ $staffUsers->total() != 1 ? 's' : '' }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffUsers as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <span class="badge bg-info">Staff</span>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $user->email }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.staff.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x display-4 d-block mb-2"></i>
                                <p class="mt-2">No staff members found</p>
                                <a href="{{ route('admin.staff.create') }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Add First Staff Member
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($staffUsers->hasPages())
        <div class="card-footer">
            {{ $staffUsers->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
