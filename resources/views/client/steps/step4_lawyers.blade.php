<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><i class="bi bi-people me-2"></i>Select Your Lawyer</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Based on your case analysis, we've ranked our available lawyers by their match to your needs. 
                    The percentage shows how well each lawyer's profile matches your case.
                </p>
                
                @if($lawyers->count() > 0)
                <form id="step4-form">
                    <input type="hidden" name="lawyer_id" id="selected-lawyer-id" value="">
                    
                    <div class="row">
                        @foreach($lawyers as $item)
                        @php
                            $lawyer = $item->lawyer;
                            $user = $lawyer->user;
                            $specializations = $lawyer->specializations->pluck('name')->toArray();
                        @endphp
                        <div class="col-md-6 mb-4">
                            <div class="card lawyer-card h-100" data-lawyer-id="{{ $lawyer->id }}" onclick="selectLawyer({{ $lawyer->id }})">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <i class="bi bi-person-circle me-2"></i>
                                                {{ $user->name }}
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-award me-1"></i>
                                                {{ $lawyer->years_of_experience }} years experience
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="match-score">{{ number_format($item->match_score) }}%</div>
                                            <small class="text-muted">Match Score</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Specializations -->
                                    <div class="mb-3">
                                        @foreach($specializations as $spec)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $spec }}</span>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Languages -->
                                    @if($lawyer->languages)
                                    <p class="small mb-3">
                                        <i class="bi bi-translate me-1"></i>
                                        <strong>Languages:</strong> {{ implode(', ', $lawyer->languages) }}
                                    </p>
                                    @endif
                                    
                                    <!-- Score Breakdown -->
                                    <div class="score-breakdown">
                                        <a class="text-decoration-none small" data-bs-toggle="collapse" href="#breakdown-{{ $lawyer->id }}">
                                            <i class="bi bi-chevron-down me-1"></i> View Score Breakdown
                                        </a>
                                        <div class="collapse mt-2" id="breakdown-{{ $lawyer->id }}">
                                            @foreach($item->formatted_breakdown as $factor)
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between small">
                                                    <span>{{ $factor['label'] }}</span>
                                                    <span>{{ $factor['score'] }}/{{ $factor['max'] }}</span>
                                                </div>
                                                <div class="score-bar">
                                                    <div class="score-bar-fill" style="width: {{ $factor['percentage'] }}%"></div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" class="form-check-input me-2" name="lawyer_radio" 
                                               id="lawyer-{{ $lawyer->id }}" value="{{ $lawyer->id }}">
                                        <label class="form-check-label" for="lawyer-{{ $lawyer->id }}">
                                            Select this lawyer
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="alert alert-light border mt-3">
                        <h6 class="alert-heading"><i class="bi bi-question-circle me-2"></i>How is the match score calculated?</h6>
                        <p class="small mb-0">
                            The match score is based on: <strong>Specialization Match (40%)</strong>, 
                            <strong>Similar Cases Handled (15%)</strong>, <strong>Availability (15%)</strong>, 
                            <strong>Experience (10%)</strong>, <strong>Language Match (10%)</strong>, and 
                            <strong>Current Workload (10%)</strong>.
                        </p>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goBack(3)">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="submitStep(4, 'step4-form')" id="continue-btn" disabled>
                            Continue to Scheduling <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No lawyers are currently available. Please try again later or contact the office directly.
                </div>
                <button type="button" class="btn btn-outline-secondary" onclick="goBack(3)">
                    <i class="bi bi-arrow-left me-2"></i> Go Back
                </button>                @endif
            </div>
        </div>
    </div>
</div>
