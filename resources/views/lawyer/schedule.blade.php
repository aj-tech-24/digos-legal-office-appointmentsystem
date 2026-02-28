@extends('layouts.lawyer')

@section('title', 'Schedule Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Schedule Management</h1>
            <p class="text-muted mb-0">Manage your weekly hours and specific time-offs</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-calendar-week me-2"></i> Weekly Recurring Schedule</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lawyer.schedule.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%">Day</th>
                                        <th style="width: 10%" class="text-center">Active</th>
                                        <th style="width: 25%">Start Time</th>
                                        <th style="width: 25%">End Time</th>
                                        <th style="width: 25%">Max Clients</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $dayIndex => $dayName)
                                    @php
                                        $schedule = $schedules->get($dayIndex);
                                        $isActive = $schedule && $schedule->is_available;
                                    @endphp
                                    <tr class="{{ $isActive ? '' : 'bg-light text-muted' }}">
                                        <td>
                                            <span class="fw-bold">{{ $dayName }}</span>
                                            <input type="hidden" name="schedules[{{ $dayIndex }}][day_of_week]" value="{{ $dayIndex }}">
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input availability-toggle" 
                                                       name="schedules[{{ $dayIndex }}][is_available]" 
                                                       value="1"
                                                       data-day="{{ $dayIndex }}"
                                                       {{ $isActive ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm" 
                                                   name="schedules[{{ $dayIndex }}][start_time]" 
                                                   id="start_{{ $dayIndex }}"
                                                   value="{{ $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00' }}"
                                                   {{ $isActive ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm" 
                                                   name="schedules[{{ $dayIndex }}][end_time]" 
                                                   id="end_{{ $dayIndex }}"
                                                   value="{{ $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '17:00' }}"
                                                   {{ $isActive ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   name="schedules[{{ $dayIndex }}][max_appointments]" 
                                                   id="max_{{ $dayIndex }}"
                                                   min="1" max="20"
                                                   value="{{ $schedule ? $schedule->max_appointments : 8 }}"
                                                   {{ $isActive ? '' : 'disabled' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Weekly Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
        <div class="card shadow-sm mb-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0"><i class="bi bi-calendar-x me-2"></i> Special Time Off</h6>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">Add specific dates where you cannot accept appointments.</p>
        
        <form action="{{ route('lawyer.schedule.unavailability.store') }}" method="POST" class="mb-4">
            @csrf
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Select Date</label>
                <input type="date" name="unavailable_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
            </div>

            <input type="hidden" name="type" value="whole">
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Reason/s</label>
                <textarea name="reason" class="form-control" rows="4" placeholder="Enter reason here..." required></textarea>
                <div class="form-text text-xs">You can list reasons here (Max 255 characters).</div>
            </div>

            <button type="submit" class="btn btn-danger btn-sm w-100">
                <i class="bi bi-plus-circle me-1"></i> Add Unavailable Date
            </button>
        </form>

        <hr>

        <h6 class="small fw-bold text-muted mb-2">Upcoming Off Dates</h6>
        @if(isset($unavailabilities) && $unavailabilities->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($unavailabilities as $off)
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-danger">
                                {{ \Carbon\Carbon::parse($off->unavailable_date)->format('M d, Y') }}
                            </div>
                            <div class="small text-dark mt-1" style="white-space: pre-wrap;">{{ $off->reason }}</div>
                        </div>
                        <form action="{{ route('lawyer.schedule.unavailability.destroy', $off->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0" onclick="return confirm('Remove this off date?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted small py-2">
                No upcoming time-offs scheduled.
            </div>
        @endif
    </div>
</div>

            <div class="card shadow-sm bg-light border-0">
                <div class="card-body">
                    <h6 class="mb-3 fw-bold"><i class="bi bi-lightbulb me-2"></i> Scheduling Tips</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-1">Uncheck the "Active" box to mark a specific day of the week as always closed (e.g., Weekends).</li>
                        <li class="mb-1">Use "Special Time Off" for specific dates without changing your weekly settings.</li>
                        <li>Clients cannot book appointments on dates marked as "Time Off".</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle time inputs based on availability checkbox
        const toggles = document.querySelectorAll('.availability-toggle');
        
        toggles.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const dayIndex = this.dataset.day;
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('input[type="time"], input[type="number"]');
                
                if (this.checked) {
                    row.classList.remove('bg-light', 'text-muted');
                    inputs.forEach(input => input.disabled = false);
                } else {
                    row.classList.add('bg-light', 'text-muted');
                    inputs.forEach(input => input.disabled = true);
                }
            });
        });
    });
</script>
@endpush