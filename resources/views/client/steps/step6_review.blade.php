<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Review Your Appointment</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Please review all the details below before submitting your appointment request.
                </div>
                
                @php
                    $state = $draft->draft_state ?? [];
                @endphp
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Your Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">Name:</td>
                                        <td><strong>{{ $state['first_name'] ?? '' }} {{ $state['last_name'] ?? '' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email:</td>
                                        <td>{{ $state['email'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td>{{ $state['phone'] ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Address:</td>
                                        <td>{{ $state['address'] ?? 'Not provided' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">Date:</td>
                                        <td><strong>{{ \Carbon\Carbon::parse($state['appointment_date'] ?? '')->format('F j, Y') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Time:</td>
                                        <td>
                                            @php
                                                $time = $state['appointment_time'] ?? '';
                                                $times = explode('-', $time);
                                                $displayTime = count($times) === 2 
                                                    ? \Carbon\Carbon::parse($times[0])->format('g:i A') . ' - ' . \Carbon\Carbon::parse($times[1])->format('g:i A')
                                                    : $time;
                                            @endphp
                                            <strong>{{ $displayTime }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Duration:</td>
                                        <td>{{ $recommendation->estimated_duration_minutes ?? 60 }} minutes</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Your Lawyer</h5>
                            </div>
                            <div class="card-body">
                                @if($lawyer)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">{{ $lawyer->user->name }}</h5>
                                        <small class="text-muted">{{ $lawyer->years_of_experience }} years experience</small>
                                    </div>
                                </div>
                                <div>
                                    @foreach($lawyer->specializations as $spec)
                                    <span class="badge bg-secondary me-1">{{ $spec->name }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Case Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">Category:</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $recommendation->detected_services['primary'] ?? 'General' }}</span>
                                            @if(!empty($recommendation->detected_services['secondary']))
                                            <span class="badge bg-secondary">{{ $recommendation->detected_services['secondary'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Complexity:</td>
                                        <td>{{ ucfirst($recommendation->complexity_level ?? 'Moderate') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Case Summary</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $recommendation->professional_summary ?? '' }}</p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="goBack(5)">
                        <i class="bi bi-arrow-left me-2"></i> Back
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="submitBooking()">
                        <i class="bi bi-check-circle me-2"></i> Confirm Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>