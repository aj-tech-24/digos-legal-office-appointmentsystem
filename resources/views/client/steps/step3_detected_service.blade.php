<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><i class="bi bi-robot me-2"></i>AI Case Analysis Results</h4>
            </div>
            <div class="card-body">
                @if($recommendation)
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle me-2"></i>
                    Our AI has analyzed your case. Please review the results below.
                </div>



                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Case Summary</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $recommendation->professional_summary }}</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Detected Legal Categories</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $services = $recommendation->detected_services ?? [];
                                    $primary = $services['primary'] ?? ($services[0] ?? 'General Consultation');
                                    $secondary = $services['secondary'] ?? ($services[1] ?? null);
                                @endphp
                                
                                <div class="mb-3">
                                    <span class="badge bg-primary fs-6 me-2">
                                        <i class="bi bi-star-fill me-1"></i> Primary
                                    </span>
                                    <span class="fs-5">{{ $primary }}</span>
                                </div>
                                
                                @if($secondary)
                                <div>
                                    <span class="badge bg-secondary fs-6 me-2">Secondary</span>
                                    <span class="fs-5">{{ $secondary }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Case Assessment</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="fs-4 fw-bold text-primary">
                                            {{ ucfirst($recommendation->complexity_level) }}
                                        </div>
                                        <div class="text-muted small">Complexity Level</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="fs-4 fw-bold text-primary">
                                            60 mins
                                        </div>
                                        <div class="text-muted small">Standard Duration</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>How is this analysis generated?</strong><br>
                    Our AI analyzes your narrative to identify the type of legal service you need and estimate complexity. This helps us match you with the most suitable lawyer.
                </div>
                
                <form id="step3-form">
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="confirm_analysis" id="confirm_analysis" value="1">
                        <label class="form-check-label" for="confirm_analysis">
                            I confirm that the above analysis reflects my legal concern and I wish to proceed.
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="goBack(2)">
                            <i class="bi bi-arrow-left me-2"></i> Edit My Narrative
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="submitStep(3, 'step3-form')">
                            View Recommended Lawyers <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
                
                @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Unable to analyze your case. Please go back and try again.
                </div>
                <button type="button" class="btn btn-outline-secondary" onclick="goBack(2)">
                    <i class="bi bi-arrow-left me-2"></i> Go Back
                </button>
                @endif
            </div>
        </div>
    </div>
</div>