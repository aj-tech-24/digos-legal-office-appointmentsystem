<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Select Your Appointment Date & Time</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Choose a date and available time slot for your consultation. 
                    @if($recommendation)
                    Based on your case complexity, each consultation will be <strong>{{ $recommendation->estimated_duration_minutes }} minutes</strong>.
                    @endif
                </p>
                
                <form id="step5-form">
                    <div class="row">
                        <!-- Date Picker -->
                        <div class="col-md-5 mb-4">
                            <label for="appointment_date" class="form-label fw-bold">
                                <i class="bi bi-calendar-event me-2"></i>Select Date
                            </label>
                            <input type="date" class="form-control form-control-lg" id="appointment_date" 
                                   name="appointment_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   max="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                            <div class="form-text">You can book up to 30 days in advance.</div>
                        </div>
                        
                        <!-- Time Slots -->
                        <div class="col-md-7 mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clock me-2"></i>Available Time Slots
                            </label>
                            <input type="hidden" name="appointment_time" id="appointment_time" value="">
                            
                            <div id="time-slots-container">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Please select a date first to see available time slots.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-clock-history me-2"></i>Office Hours</h6>
                                <p class="mb-0 small">Monday - Friday: 8:00 AM - 5:00 PM</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-info-circle me-2"></i>Note</h6>
                                <p class="mb-0 small">Please arrive 15 minutes before your scheduled time.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goBack(4)">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="submitStep(5, 'step5-form')" id="continue-btn" disabled>
                            Review Appointment <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const dateInput = document.getElementById('appointment_date');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const appointmentTimeInput = document.getElementById('appointment_time');
    const continueBtn = document.getElementById('continue-btn');
    const lawyerId = {{ $lawyerId ?? 'null' }};
    
    dateInput.addEventListener('change', async function() {
        if (!this.value || !lawyerId) return;
        
        timeSlotsContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2">Loading available slots...</span>
            </div>
        `;
          try {
            const res = await fetch(`/book/time-slots?lawyer_id=${lawyerId}&date=${this.value}&session_id=${sessionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                credentials: 'same-origin'
            });
            
            const data = await res.json();
            
            if (data.success && data.slots.length > 0) {
                let html = '<div class="row g-2">';
                data.slots.forEach(slot => {
                    html += `
                        <div class="col-6 col-md-4">
                            <div class="time-slot btn btn-outline-secondary w-100 py-2" 
                                 data-value="${slot.value}" onclick="selectTimeSlot(this, '${slot.value}')">
                                ${slot.display}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                if (data.duration) {
                    html += `<p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i>Each slot is ${data.duration} minutes based on your case complexity.</p>`;
                }
                
                timeSlotsContainer.innerHTML = html;
            } else {
                timeSlotsContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'No available time slots for this date. Please select another date.'}
                    </div>
                `;
            }
              // Reset selection
            appointmentTimeInput.value = '';
            continueBtn.disabled = true;
            
        } catch (error) {
            console.error('Error:', error);
            timeSlotsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Failed to load time slots. Please try again.
                </div>
            `;
        }
    });
</script>
