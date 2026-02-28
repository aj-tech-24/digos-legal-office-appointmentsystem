@extends('layouts.staff')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Appointment Management</h1>
    </div>

    {{-- Clean Filter Section --}}
    <div class="card shadow-sm mb-4 border-left-primary">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('staff.appointments.index') }}">
                <div class="form-row align-items-center">
                    
                    {{-- Search --}}
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-search text-gray-400"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control bg-light border-0 small" 
                                placeholder="Search Client or Ref #..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-2 mb-2 mb-md-0">
                        <select name="status" class="form-control custom-select">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\Appointment::STATUS_LABELS as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lawyer Filter --}}
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="lawyer_id" class="form-control custom-select">
                            <option value="">All Lawyers</option>
                            @foreach($lawyers as $lawyer)
                                <option value="{{ $lawyer->id }}" {{ request('lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                                    Atty. {{ $lawyer->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Filter --}}
                    <div class="col-md-2 mb-2 mb-md-0">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1">
                            Filter
                        </button>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-secondary btn-sm" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Appointments Table --}}
    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                    <thead class="bg-light text-gray-600">
                        <tr>
                            <th class="py-3 pl-4">Ref #</th>
                            <th class="py-3">Client</th>
                            <th class="py-3">Schedule</th>
                            <th class="py-3">Assigned To</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            <td class="pl-4 align-middle">
                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="font-weight-bold text-primary">
                                    {{ $appointment->reference_number }}
                                </a>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $appointment->clientRecord->full_name }}</div>
                                <small class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i> {{ $appointment->clientRecord->phone }}</small>
                            </td>
                            <td class="align-middle">
                                <div>{{ $appointment->start_datetime->format('M d, Y') }}</div>
                                <small class="text-info font-weight-bold">{{ $appointment->start_datetime->format('h:i A') }}</small>
                            </td>
                            <td class="align-middle">
                                @if($appointment->lawyer)
                                    Atty. {{ $appointment->lawyer->user->last_name }}
                                @else
                                    <span class="text-muted font-italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-pill badge-{{ $appointment->status_color }} px-2 py-1">
                                    {{ $appointment->status_label }}
                                </span>
                            </td>
                            <td class="align-middle text-right pr-4">
                                {{-- DYNAMIC ACTION DROPDOWN --}}
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                            id="dropdownMenuButton{{ $appointment->id }}" data-toggle="dropdown" 
                                            aria-haspopup="true" aria-expanded="false">
                                        Manage
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                                         aria-labelledby="dropdownMenuButton{{ $appointment->id }}">
                                        
                                        {{-- 1. View Details (Always Available) --}}
                                        <a class="dropdown-item" href="{{ route('staff.appointments.show', $appointment->id) }}">
                                            <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Details
                                        </a>

                                        <div class="dropdown-divider"></div>

                                        {{-- 2. Pending Actions --}}
                                        @if($appointment->status === 'pending')
                                            <a class="dropdown-item text-success" href="{{ route('staff.appointments.show', $appointment->id) }}">
                                                <i class="fas fa-check fa-sm fa-fw mr-2"></i> Review & Confirm
                                            </a>
                                            <button class="dropdown-item text-danger" data-toggle="modal" data-target="#declineModal-{{ $appointment->id }}">
                                                <i class="fas fa-times fa-sm fa-fw mr-2"></i> Decline
                                            </button>
                                        @endif

                                        {{-- 3. Confirmed Actions --}}
                                        @if($appointment->status === 'confirmed')
                                            <form action="{{ route('staff.appointments.check-in', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-primary">
                                                    <i class="fas fa-user-check fa-sm fa-fw mr-2"></i> Check In
                                                </button>
                                            </form>
                                            <form action="{{ route('staff.appointments.cancel', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Are you sure you want to cancel?')">
                                                    <i class="fas fa-ban fa-sm fa-fw mr-2"></i> Cancel
                                                </button>
                                            </form>
                                        @endif

                                        {{-- 4. Checked In Actions --}}
                                        @if($appointment->status === 'checked_in')
                                            <form action="{{ route('staff.appointments.start', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-info">
                                                    <i class="fas fa-play fa-sm fa-fw mr-2"></i> Start Consultation
                                                </button>
                                            </form>
                                            <form action="{{ route('staff.appointments.no-show', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-secondary">
                                                    <i class="fas fa-user-slash fa-sm fa-fw mr-2"></i> Mark No-Show
                                                </button>
                                            </form>
                                        @endif

                                        {{-- 5. In Progress Actions --}}
                                        @if($appointment->status === 'in_progress')
                                            <form action="{{ route('staff.appointments.complete', $appointment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-check-circle fa-sm fa-fw mr-2"></i> Complete
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </div>

                                {{-- Decline Modal (Only renders if status is pending to save DOM size) --}}
                                @if($appointment->status === 'pending')
                                <div class="modal fade text-left" id="declineModal-{{ $appointment->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title text-danger">Decline Appointment</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('staff.appointments.decline', $appointment->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to decline <strong>{{ $appointment->clientRecord->full_name }}</strong>?</p>
                                                    <div class="form-group">
                                                        <label>Reason for declining <span class="text-danger">*</span></label>
                                                        <textarea name="decline_reason" class="form-control" rows="3" required placeholder="e.g. Lawyer unavailable, Conflict of interest..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                    <button class="btn btn-danger" type="submit">Decline Appointment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                {{-- End Modal --}}

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-times fa-2x mb-3 text-gray-300"></i>
                                <p class="mb-0">No appointments found matching your filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($appointments->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection