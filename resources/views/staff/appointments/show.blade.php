@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointment Details</h1>
        <a href="{{ route('staff.appointments.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Reference: {{ $appointment->reference_number }}</h6>
                    <span class="badge badge-{{ $appointment->status_color }} px-3 py-2">{{ $appointment->status_label }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold text-muted">CLIENT INFORMATION</h5>
                            <p class="h5 text-dark">{{ $appointment->clientRecord->full_name }}</p>
                            <p class="mb-1"><i class="fas fa-envelope mr-2 text-gray-400"></i> {{ $appointment->clientRecord->email }}</p>
                            <p class="mb-1"><i class="fas fa-phone mr-2 text-gray-400"></i> {{ $appointment->clientRecord->phone }}</p>
                            <p><i class="fas fa-map-marker-alt mr-2 text-gray-400"></i> {{ $appointment->clientRecord->address ?? 'No Address Recorded' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold text-muted">APPOINTMENT INFO</h5>
                            <p class="mb-1"><strong>Date:</strong> {{ $appointment->start_datetime->format('F j, Y') }}</p>
                            <p class="mb-1"><strong>Time:</strong> {{ $appointment->start_datetime->format('h:i A') }} - {{ $appointment->end_datetime->format('h:i A') }}</p>
                            <p class="mb-1"><strong>Lawyer:</strong> Atty. {{ $appointment->lawyer->user->name }}</p>
                            <p><strong>Service:</strong> {{ $appointment->detected_services['primary'] ?? 'General Consultation' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5 class="small font-weight-bold text-primary">REQUIRED DOCUMENTS (CHECKLIST)</h5>
                        @if(!empty($appointment->document_checklist) && is_array($appointment->document_checklist))
                            <ul class="list-group">
                                @foreach($appointment->document_checklist as $doc)
                                    <li class="list-group-item py-2">
                                        <i class="far fa-check-square text-success mr-2"></i> 
                                        {{ is_array($doc) ? ($doc['item'] ?? 'Requirement') : $doc }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-secondary text-center small">
                                No specific document checklist was assigned during confirmation.
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h5 class="small font-weight-bold text-muted">CLIENT'S NARRATIVE</h5>
                        <div class="p-3 bg-light rounded border">
                            {{ $appointment->narrative }}
                        </div>
                    </div>

                    @if($appointment->admin_notes)
                    <div class="mb-4">
                        <h5 class="small font-weight-bold text-muted">ADMIN / STAFF NOTES</h5>
                        <div class="p-3 bg-light rounded border border-left-info">
                            {!! nl2br(e($appointment->admin_notes)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    @if($appointment->status === 'pending')
                        <button class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#confirmModal">
                            <i class="fas fa-check"></i> Confirm Appointment
                        </button>
                        <button class="btn btn-danger btn-block" data-toggle="modal" data-target="#declineModal">
                            <i class="fas fa-times"></i> Decline Request
                        </button>

                    @elseif($appointment->status === 'confirmed')
                        @if(is_null($appointment->checked_in_at))
                            <form action="{{ route('staff.appointments.checkIn', $appointment->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-block mb-3">
                                    <i class="fas fa-user-check"></i> Check-In Client
                                </button>
                            </form>
                        @else
                             <div class="alert alert-info text-center">Client Checked In</div>
                        @endif
                        
                        <form action="{{ route('staff.appointments.cancel', $appointment->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger btn-block btn-sm">Cancel Appointment</button>
                        </form>

                    @elseif($appointment->status === 'in_progress')
                        <div class="alert alert-primary text-center">Session In Progress</div>
                        <form action="{{ route('staff.appointments.complete', $appointment->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-flag-checkered"></i> Mark Completed
                            </button>
                        </form>

                    @elseif($appointment->status === 'completed')
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle"></i> Appointment Completed
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">Add Case Note</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.appointments.addNote', $appointment->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="small">Note Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Initial Screening" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="small">Link to Booking Date (Optional)</label>
                            <select name="linked_date" class="form-control form-control-sm">
                                <option value="">-- General Note --</option>
                                <option value="{{ $appointment->start_datetime->toDateString() }}" selected>
                                    Current: {{ $appointment->start_datetime->format('M d, Y') }}
                                </option>
                                @foreach($clientHistory as $history)
                                    @if($history->id !== $appointment->id)
                                        <option value="{{ $history->start_datetime->toDateString() }}">
                                            Past: {{ $history->start_datetime->format('M d, Y') }} ({{ $history->reference_number }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small">Content</label>
                            <textarea name="content" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block btn-sm">Save Note</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('staff.appointments.confirm', $appointment->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Assign Requirements for the client to bring:</p>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="req1" name="requirements[]" value="Valid ID">
                            <label class="custom-control-label" for="req1">Valid ID</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="req2" name="requirements[]" value="Proof of Indigency">
                            <label class="custom-control-label" for="req2">Proof of Indigency</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="req3" name="requirements[]" value="Case Related Documents">
                            <label class="custom-control-label" for="req3">Case Related Documents</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Additional Instructions</label>
                        <textarea name="instructions" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Confirm & Send Email</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="declineModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('staff.appointments.decline', $appointment->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Decline Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for declining <span class="text-danger">*</span></label>
                        <textarea name="decline_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline Appointment</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection