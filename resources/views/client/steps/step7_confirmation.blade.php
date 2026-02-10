<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-success">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                </div>
                
                <h2 class="text-success mb-3">Appointment Request Submitted!</h2>
                
                <p class="lead mb-4">
                    Your appointment request has been submitted and is now pending review. 
                    You will receive an email notification once it has been approved.
                </p>
                
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Reference Number</h5>
                        <div class="display-6 text-primary fw-bold">{{ $appointment->reference_number }}</div>
                        <p class="text-muted small mt-2 mb-0">Please save this reference number for your records.</p>
                    </div>
                </div>
                
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment Details</h5>
                            </div>
                            <div class="card-body text-start">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">Date:</td>
                                        <td><strong>{{ $appointment->start_datetime->format('F j, Y') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Time:</td>
                                        <td><strong>{{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Lawyer:</td>
                                        <td>{{ $appointment->lawyer->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status:</td>
                                        <td><span class="badge bg-warning text-dark">Pending Review</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info text-start">
                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>What's Next?</h6>
                    <ol class="mb-0">
                        <li>You will receive an acknowledgment email at <strong>{{ $clientRecord->email }}</strong></li>
                        <li>Your appointment request will be reviewed by our staff or assigned lawyer</li>
                        <li>Once approved, you will receive a <strong>confirmation email</strong> with final details</li>
                        <li>If approved, please arrive 15 minutes before your scheduled time</li>
                        <li>Don't forget to bring the required documents</li>
                    </ol>
                </div>
                
                <div class="alert alert-warning text-start">
                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Important Note</h6>
                    <p class="mb-0">
                        Your appointment is <strong>not yet confirmed</strong>. Please wait for the confirmation email 
                        before visiting our office. We will notify you within 1-2 business days.
                    </p>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <a href="/" class="btn btn-outline-primary">
                        <i class="bi bi-house me-2"></i>Return to Home
                    </a>
                    <a href="/book" class="btn btn-primary">
                        <i class="bi bi-calendar-plus me-2"></i>Book Another Appointment
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted">
                <i class="bi bi-question-circle me-1"></i>
                Questions? Contact us at <a href="mailto:legal@digoscity.gov.ph">legal@digoscity.gov.ph</a>
            </p>
        </div>
    </div>
</div>
