@extends('layouts.admin')

@section('title', 'Client: ' . $clientRecord->full_name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Client Records</a></li>
                    <li class="breadcrumb-item active">{{ $clientRecord->full_name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ $clientRecord->full_name }}</h1>
        </div>
        <div>
            <a href="{{ route('admin.clients.print', $clientRecord) }}" target="_blank" class="btn btn-outline-dark me-2">
                <i class="bi bi-printer me-1"></i> Print Record
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="bi bi-plus-lg me-1"></i> Add Note
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Client Info -->
        <div class="col-lg-4">
            <!-- Client Profile Card -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i> Client Profile</h5>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editClientModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($clientRecord->first_name, 0, 1) . substr($clientRecord->last_name, 0, 1)) }}
                        </div>
                        <h5 class="mt-3 mb-1">{{ $clientRecord->full_name }}</h5>
                        @switch($clientRecord->status)
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
                    </div>

                    <hr>

                    <dl class="row mb-0">
                        @if($clientRecord->email)
                        <dt class="col-sm-4"><i class="bi bi-envelope text-muted"></i></dt>
                        <dd class="col-sm-8">{{ $clientRecord->email }}</dd>
                        @endif

                        @if($clientRecord->phone)
                        <dt class="col-sm-4"><i class="bi bi-telephone text-muted"></i></dt>
                        <dd class="col-sm-8">{{ $clientRecord->phone }}</dd>
                        @endif

                        @if($clientRecord->address)
                        <dt class="col-sm-4"><i class="bi bi-geo-alt text-muted"></i></dt>
                        <dd class="col-sm-8">{{ $clientRecord->address }}</dd>
                        @endif

                        @if($clientRecord->case_number)
                        <dt class="col-sm-4"><i class="bi bi-folder text-muted"></i></dt>
                        <dd class="col-sm-8">
                            <code class="bg-light text-dark px-2 py-1 rounded">{{ $clientRecord->case_number }}</code>
                        </dd>
                        @endif

                        <dt class="col-sm-4"><i class="bi bi-calendar text-muted"></i></dt>
                        <dd class="col-sm-8">Client since {{ $clientRecord->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Status Management Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i> Status Management</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.clients.updateStatus', $clientRecord) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="d-flex gap-2">
                            <select name="status" class="form-select">
                                <option value="active" {{ $clientRecord->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="closed" {{ $clientRecord->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="archived" {{ $clientRecord->status == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Appointments Summary Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i> Appointments ({{ $clientRecord->appointments->count() }})</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($clientRecord->appointments->take(5) as $appointment)
                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $appointment->start_datetime->format('M d, Y') }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $appointment->start_datetime->format('g:i A') }} - 
                                    {{ $appointment->lawyer->user->name ?? 'No lawyer' }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $appointment->status_color }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-center text-muted py-4">
                        No appointments yet
                    </div>
                    @endforelse
                </div>
                @if($clientRecord->appointments->count() > 5)
                <div class="card-footer text-center bg-white">
                    <a href="{{ route('admin.appointments.index', ['search' => $clientRecord->email ?? $clientRecord->full_name]) }}" class="btn btn-sm btn-link">
                        View All {{ $clientRecord->appointments->count() }} Appointments
                    </a>
                </div>
                @endif
            </div>

            <!-- Documents Card -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-file-earmark me-2"></i> Documents ({{ $clientRecord->documents->count() }})</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($clientRecord->documents as $document)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-text text-muted me-2"></i>
                                {{ $document->original_filename }}
                                <br>
                                <small class="text-muted">{{ $document->document_type }}</small>
                            </div>
                            @if($document->verified_at)
                                <span class="badge bg-success"><i class="bi bi-check"></i> Verified</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-4">
                        No documents uploaded
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column - Timeline -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Activity Timeline</h5>
                </div>
                <div class="card-body">
                    @if($clientRecord->entries->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history display-4 text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No Timeline Entries</h5>
                            <p class="text-muted">Timeline entries will appear here as activities occur.</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($clientRecord->entries as $entry)
                            <div class="timeline-item pb-4 {{ !$loop->last ? 'border-start' : '' }} ps-4 position-relative">
                                <!-- Timeline Dot -->
                                <div class="timeline-dot position-absolute bg-{{ $entry->type_color }} rounded-circle"
                                     style="width: 12px; height: 12px; left: -6px; top: 5px;"></div>
                                
                                <!-- Timeline Content -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-{{ $entry->type_color }} me-2">
                                                    <i class="bi {{ $entry->type_icon }} me-1"></i>
                                                    {{ $entry->type_label }}
                                                </span>
                                                <strong>{{ $entry->title }}</strong>
                                            </div>
                                            <small class="text-muted">
                                                {{ $entry->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        
                                        <p class="mb-2 text-muted">{{ $entry->content }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                @if($entry->creator)
                                                    <i class="bi bi-person"></i> {{ $entry->creator->name }}
                                                @else
                                                    <i class="bi bi-robot"></i> System
                                                @endif
                                            </small>
                                            @if($entry->appointment)
                                            <a href="{{ route('admin.appointments.show', $entry->appointment) }}" class="btn btn-sm btn-link p-0">
                                                View Appointment <i class="bi bi-arrow-right"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.clients.addNote', $clientRecord) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required placeholder="Brief description...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" rows="4" required placeholder="Detailed notes..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Appointment (Optional)</label>
                        <select name="appointment_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($clientRecord->appointments as $appointment)
                            <option value="{{ $appointment->id }}">
                                {{ $appointment->start_datetime->format('M d, Y g:i A') }} - 
                                {{ $appointment->lawyer->user->name ?? 'Unknown' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.clients.update', $clientRecord) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Client Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" 
                                   value="{{ $clientRecord->first_name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" 
                                   value="{{ $clientRecord->last_name }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" 
                               value="{{ $clientRecord->middle_name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="{{ $clientRecord->email }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" 
                               value="{{ $clientRecord->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2">{{ $clientRecord->address }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" class="form-control" name="reference_number" 
                               value="{{ $clientRecord->reference_number }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    margin-left: 6px;
}
</style>
@endsection
