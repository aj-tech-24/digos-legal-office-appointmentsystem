@extends('layouts.lawyer')

@section('title', 'Schedule Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Schedule Management</h1>
            <p class="text-muted mb-0">Set your weekly availability for appointments</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i> Weekly Schedule</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lawyer.schedule.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="150">Day</th>
                                        <th width="100" class="text-center">Available</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th width="120">Max Appointments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $dayIndex => $dayName)
                                    @php
                                        $schedule = $schedules->get($dayIndex);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $dayName }}</strong>
                                            <input type="hidden" name="schedules[{{ $dayIndex }}][day_of_week]" value="{{ $dayIndex }}">
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input availability-toggle" 
                                                       name="schedules[{{ $dayIndex }}][is_available]" 
                                                       value="1"
                                                       data-day="{{ $dayIndex }}"
                                                       {{ $schedule && $schedule->is_available ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control time-input" 
                                                   name="schedules[{{ $dayIndex }}][start_time]" 
                                                   id="start_{{ $dayIndex }}"
                                                   value="{{ $schedule && $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00' }}"
                                                   {{ $schedule && $schedule->is_available ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control time-input" 
                                                   name="schedules[{{ $dayIndex }}][end_time]" 
                                                   id="end_{{ $dayIndex }}"
                                                   value="{{ $schedule && $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '17:00' }}"
                                                   {{ $schedule && $schedule->is_available ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="schedules[{{ $dayIndex }}][max_appointments]" 
                                                   id="max_{{ $dayIndex }}"
                                                   min="1" 
                                                   max="20" 
                                                   value="{{ $schedule && $schedule->max_appointments ? $schedule->max_appointments : 8 }}"
                                                   {{ $schedule && $schedule->is_available ? '' : 'disabled' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Save Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Schedule Tips -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Set your regular weekly availability</li>
                        <li class="mb-2">Appointments will only be bookable during your available hours</li>
                        <li class="mb-2">Max appointments limits how many can be booked per day</li>
                        <li class="mb-2">Consider buffer time between appointments</li>
                        <li>Clients can book up to 14 days in advance</li>
                    </ul>
                </div>
            </div>

            <!-- Current Availability Summary -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> Current Availability</h6>
                </div>
                <div class="list-group list-group-flush">
                    @php $hasAnySchedule = false; @endphp
                    @foreach($daysOfWeek as $dayIndex => $dayName)
                        @php $schedule = $schedules->get($dayIndex); @endphp
                        @if($schedule && $schedule->is_available)
                            @php $hasAnySchedule = true; @endphp
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $dayName }}</strong>
                                    <span class="text-muted">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    Max {{ $schedule->max_appointments }} appointments
                                </small>
                            </div>
                        @endif
                    @endforeach
                    
                    @if(!$hasAnySchedule)
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-calendar-x d-block mb-2 fs-3"></i>
                            No availability set yet
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle time inputs based on availability checkbox
    document.querySelectorAll('.availability-toggle').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const dayIndex = this.dataset.day;
            const startInput = document.getElementById('start_' + dayIndex);
            const endInput = document.getElementById('end_' + dayIndex);
            const maxInput = document.getElementById('max_' + dayIndex);
            
            if (this.checked) {
                startInput.disabled = false;
                endInput.disabled = false;
                maxInput.disabled = false;
            } else {
                startInput.disabled = true;
                endInput.disabled = true;
                maxInput.disabled = true;
            }
        });
    });
</script>
@endpush
