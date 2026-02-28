<style>
    :root { 
        --color-gov-navy: #002357; 
    }

    /* Grid Layout for the container */
    .time-slot-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important; /* 3 Columns */
        gap: 12px !important;
        width: 100%;
        margin-top: 15px;
    }

    /* Button Style - Side by Side (Row) Layout */
    .time-slot-btn {
        display: flex !important;
        flex-direction: row !important; /* Icon Left, Text Right */
        align-items: center;
        justify-content: center;
        padding: 12px 15px !important;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        min-height: 50px; /* Standard height */
        width: 100%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Icon Styling */
    .time-slot-btn i {
        font-size: 1.1rem;
        margin-right: 8px !important; /* Space between icon and text */
        margin-bottom: 0 !important;   /* Remove bottom margin */
        color: #6c757d;
    }

    /* Text Styling */
    .time-slot-btn span {
        font-size: 0.9rem;
        font-weight: 600;
        color: #495057;
        white-space: nowrap; /* Prevent text wrapping */
    }

    /* Hover Effect */
    .time-slot-btn:hover:not(.disabled) {
        border-color: var(--color-gov-navy);
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Active / Selected State */
    .time-slot-btn.active {
        background-color: var(--color-gov-navy) !important;
        border-color: var(--color-gov-navy) !important;
    }

    .time-slot-btn.active span, 
    .time-slot-btn.active i {
        color: white !important;
    }

    /* Disabled State */
    .time-slot-btn.disabled {
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Mobile Responsive: 2 Columns on smaller screens */
    @media (max-width: 768px) {
        .time-slot-grid { 
            grid-template-columns: repeat(2, 1fr) !important; 
        }
        .time-slot-btn {
            padding: 10px !important;
        }
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary-custom text-white" style="background-color: var(--color-gov-navy);">
                <h4 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Select Your Appointment Date & Time</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    Choose a date and available time slot for your consultation. 
                    @if(isset($recommendation) && $recommendation)
                        Each consultation is allocated <strong>60 minutes</strong>.
                    @endif
                </p>
                
                <form id="step5-form">
                    <div class="row">
                        <div class="col-md-5 mb-4">
                            <label for="appointment_date" class="form-label fw-bold">
                                <i class="bi bi-calendar-event me-2"></i>Select Date
                            </label>
                            <input type="text" class="form-control form-control-lg bg-white" id="appointment_date" 
                                   name="appointment_date" placeholder="Select Date" readonly required>
                            <div class="form-text mt-2">You can book up to 30 days in advance.</div>
                        </div>
                        
                        <div class="col-md-7 mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clock me-2"></i>Available Time Slots
                            </label>
                            <input type="hidden" name="appointment_time" id="appointment_time" value="">
                            
                            <div id="time-slots-container">
                                <div class="alert alert-info border-0 bg-light">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Please select a date first to see available time slots.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border rounded-3 mt-2">
                        <div class="row">
                            <div class="col-md-6 border-end-md">
                                <h6><i class="bi bi-clock-history me-2"></i>Office Hours</h6>
                                <p class="mb-0 small text-muted">Monday - Friday: 8:00 AM - 5:00 PM</p>
                            </div>
                            <div class="col-md-6 ps-md-3">
                                <h6><i class="bi bi-info-circle me-2"></i>Note</h6>
                                <p class="mb-0 small text-muted">Please arrive 10 minutes before your scheduled time.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goBack(4)">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4" onclick="submitStep(5, 'step5-form')" id="continue-btn" disabled style="background-color: var(--color-gov-navy); border-color: var(--color-gov-navy);">
                            Review Appointment <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    {
        const dateInput = document.getElementById('appointment_date');
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const appointmentTimeInput = document.getElementById('appointment_time');
        const continueBtn = document.getElementById('continue-btn');
        const lawyerId = {{ $lawyerId ?? 'null' }};
        
        // Window function for onclick handling (Safe to keep)
        window.selectTimeSlot = function(element, value) {
            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            element.classList.add('active');

            if(appointmentTimeInput) {
                appointmentTimeInput.value = value;
            }

            if(continueBtn) {
                continueBtn.disabled = false;
            }
        };

        if(dateInput) {
            dateInput.addEventListener('change', async function() {
                if (!this.value) return;
                
                if (!lawyerId) {
                    console.error("Lawyer ID is missing.");
                    timeSlotsContainer.innerHTML = '<div class="alert alert-danger">Lawyer not selected.</div>';
                    return;
                }
                
                timeSlotsContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Checking lawyer's availability...</p>
                    </div>
                `;
                
                continueBtn.disabled = true;

                try {
                    const url = `/book/time-slots?lawyer_id=${lawyerId}&date=${this.value}`;
                    
                    // CSRF Token Handling
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    const headers = { 'Accept': 'application/json' };
                    if (csrfToken) {
                        headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                    }

                    const res = await fetch(url, { headers: headers });
                    
                    if (!res.ok) throw new Error(`Server returned ${res.status}`);

                    const data = await res.json();
                    
                    if (data.success && data.slots && data.slots.length > 0) {
                        
                        // --- HTML Injection ---
                        let html = '<div class="time-slot-grid">'; 
                        
                        data.slots.forEach(slot => {
                            let isBooked = slot.is_booked || false; 
                            let disabledClass = isBooked ? 'disabled' : '';
                            // Keep exact onclick logic
                            let clickEvent = isBooked ? '' : `onclick="selectTimeSlot(this, '${slot.value}')"`;

                            html += `
                                <button type="button" 
                                        class="time-slot-btn ${disabledClass}" 
                                        ${clickEvent}
                                        data-value="${slot.value}">
                                    <i class="bi bi-clock"></i>
                                    <span>${slot.display}</span>
                                </button>
                            `;
                        });
                        html += '</div>';
                        
                        if (data.duration) {
                            html += `<div class="mt-3 text-center small text-muted"><i class="bi bi-info-circle me-1"></i>Duration: ${data.duration} mins</div>`;
                        }
                        
                        timeSlotsContainer.innerHTML = html;
                    } else {
                        timeSlotsContainer.innerHTML = `
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-calendar-x d-block fs-2 mb-2"></i>
                                No available slots for this date.
                            </div>
                        `;
                    }
                    
                } catch (error) {
                    console.error('Error fetching slots:', error);
                    timeSlotsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Could not load time slots. Please try again.
                        </div>
                    `;
                }
            });
        }
    }
</script>