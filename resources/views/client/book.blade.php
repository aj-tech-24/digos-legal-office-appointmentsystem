@extends('layouts.app')

@section('title', 'Book an Appointment - Digos City Legal Office')

@section('content')
<div class="container my-4">
    <!-- Step Indicator -->
    <div class="step-indicator" id="step-indicator">
        <div class="step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Privacy</div>
        </div>
        <div class="step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Your Case</div>
        </div>
        <div class="step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Analysis</div>
        </div>
        <div class="step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Lawyer</div>
        </div>
        <div class="step" data-step="5">
            <div class="step-number">5</div>
            <div class="step-label">Schedule</div>
        </div>
        <div class="step" data-step="6">
            <div class="step-number">6</div>
            <div class="step-label">Review</div>
        </div>
    </div>

    <!-- Step Content Container -->
    <div id="step-container">
        @include('client.steps.step1_privacy')
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = {{ $draft->current_step ?? 1 }};
    const sessionId = '{{ $sessionId }}';
    
    // Function to execute inline scripts from dynamically loaded HTML
    function executeInlineScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.body.appendChild(newScript);
            // Remove after execution to avoid duplicates
            newScript.remove();
        });
    }
    
    function updateStepIndicator(step) {
        currentStep = step;
        document.querySelectorAll('.step-indicator .step').forEach((el, index) => {
            const stepNum = index + 1;
            el.classList.remove('active', 'completed');
            
            if (stepNum === step) {
                el.classList.add('active');
            } else if (stepNum < step) {
                el.classList.add('completed');
            }
        });
    }
    
    async function submitStep(step, formId) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error('Form not found:', formId);
            return;
        }
        
        const formData = new FormData(form);
        formData.append('session_id', sessionId);
        
        showLoading(step === 2 ? 'Analyzing your case with AI...' : 'Processing...');
        
        try {
            const res = await fetch(`/book/step/${step}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('step-container').innerHTML = data.html;
                executeInlineScripts(document.getElementById('step-container'));
                updateStepIndicator(data.step);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else if (data.error) {
                    showAlert(data.error);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.');
        } finally {
            hideLoading();
        }
    }
      async function goBack(step) {
        showLoading('Going back...');
        
        const formData = new FormData();
        formData.append('session_id', sessionId);
        
        try {
            const res = await fetch(`/book/back/${step}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await res.json();
              if (data.success) {
                document.getElementById('step-container').innerHTML = data.html;
                executeInlineScripts(document.getElementById('step-container'));
                updateStepIndicator(data.step);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.');
        } finally {
            hideLoading();
        }
    }
      async function submitBooking() {
        showLoading('Submitting your appointment...');
        
        const formData = new FormData();
        formData.append('session_id', sessionId);
        
        try {
            const res = await fetch('/book/submit', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await res.json();
              if (data.success) {
                document.getElementById('step-container').innerHTML = data.html;
                document.getElementById('step-indicator').style.display = 'none';
            } else {
                showAlert(data.error || 'Failed to submit appointment.');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.');
        } finally {
            hideLoading();
        }
    }
    
    // ========================================
    // Step 4: Lawyer Selection Functions
    // ========================================
    function selectLawyer(lawyerId) {
        // Update hidden field
        const hiddenField = document.getElementById('selected-lawyer-id');
        if (hiddenField) hiddenField.value = lawyerId;
        
        // Update radio button
        const radio = document.getElementById('lawyer-' + lawyerId);
        if (radio) radio.checked = true;
        
        // Update visual selection
        document.querySelectorAll('.lawyer-card').forEach(card => {
            card.classList.remove('selected');
        });
        const selectedCard = document.querySelector(`.lawyer-card[data-lawyer-id="${lawyerId}"]`);
        if (selectedCard) selectedCard.classList.add('selected');
        
        // Enable continue button
        const continueBtn = document.getElementById('continue-btn');
        if (continueBtn) continueBtn.disabled = false;
    }
      // ========================================
    // Step 5: Time Slot Selection Functions
    // ========================================
    function selectTimeSlot(element, value) {
        // Update hidden field
        const hiddenField = document.getElementById('appointment_time');
        if (hiddenField) hiddenField.value = value;
        
        // Update visual selection
        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.classList.remove('selected', 'btn-primary');
            slot.classList.add('btn-outline-secondary');
        });
        element.classList.remove('btn-outline-secondary');
        element.classList.add('selected', 'btn-primary');
        
        // Enable continue button
        const continueBtn = document.getElementById('continue-btn');
        if (continueBtn) continueBtn.disabled = false;
    }
    
    // Initialize step indicator
    updateStepIndicator(currentStep);
</script>
@endpush
