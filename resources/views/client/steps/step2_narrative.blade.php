<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Tell Us About Your Legal Concern</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Please provide your contact information and describe your legal concern in detail. Our AI system will analyze your case to recommend the most suitable lawyer and estimate the consultation duration.
                </p>
                
                <form id="step2-form">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="{{ $draft->draft_state['first_name'] ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="{{ $draft->draft_state['last_name'] ?? '' }}" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ $draft->draft_state['email'] ?? '' }}" required>
                            <div class="form-text">We'll send your appointment confirmation here.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="{{ $draft->draft_state['phone'] ?? '' }}" placeholder="Optional">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="narrative" class="form-label">
                            <strong>Describe Your Legal Concern</strong> <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="narrative" name="narrative" rows="8" 
                                  placeholder="Please describe your legal concern in detail. Include relevant background information, what happened, when it happened, parties involved, and what outcome you are hoping for. The more details you provide, the better we can assist you."
                                  required minlength="50">{{ $draft->draft_state['narrative'] ?? '' }}</textarea>
                        <div class="form-text">
                            <span id="char-count">0</span> characters (minimum 50 required)
                        </div>
                    </div>
                    
                    <div class="alert alert-light border">
                        <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Tips for a Better Analysis</h6>
                        <ul class="mb-0 small">
                            <li>Be specific about dates, places, and people involved</li>
                            <li>Mention any documents or evidence you have</li>
                            <li>Describe what you've already done to address the issue</li>
                            <li>State clearly what kind of help you need</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goBack(1)">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="submitStep(2, 'step2-form')">
                            Analyze My Case <i class="bi bi-robot ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Character counter
    const narrative = document.getElementById('narrative');
    const charCount = document.getElementById('char-count');
    
    function updateCharCount() {
        charCount.textContent = narrative.value.length;
        if (narrative.value.length < 50) {
            charCount.classList.add('text-danger');
            charCount.classList.remove('text-success');
        } else {
            charCount.classList.remove('text-danger');
            charCount.classList.add('text-success');
        }
    }
    
    narrative.addEventListener('input', updateCharCount);
    updateCharCount();
</script>
