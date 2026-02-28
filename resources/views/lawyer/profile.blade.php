@extends('layouts.lawyer')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Profile</h1>
            <p class="text-muted mb-0">View and update your professional information</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr($lawyer->user->name, 0, 2)) }}
                    </div>
                    <h4 class="mb-1">{{ $lawyer->user->name }}</h4>
                    <p class="text-muted mb-3">{{ $lawyer->user->email }}</p>
                    
                    <span class="badge bg-success fs-6 mb-3">
                        <i class="bi bi-check-circle me-1"></i> Approved
                    </span>

                    @if($lawyer->roll_number)
                    <div class="text-muted">
                        <small>Roll No: <strong>{{ $lawyer->roll_number }}</strong></small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Specializations -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-award me-2"></i> Specializations</h6>
                </div>
                <div class="card-body">
                    @if($lawyer->specializations->count() > 0)
                        @foreach($lawyer->specializations as $specialization)
                            <span class="badge bg-primary me-1 mb-1">{{ $specialization->name }}</span>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No specializations set</p>
                    @endif
                    <p class="text-muted small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Contact admin to update specializations
                    </p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h3 class="mb-0 text-primary">{{ $lawyer->cases_handled ?? 0 }}</h3>
                            <small class="text-muted">Cases Handled</small>
                        </div>
                        <div class="col-6">
                            <h3 class="mb-0 text-success">{{ $lawyer->years_experience ?? 0 }}</h3>
                            <small class="text-muted">Years Experience</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Bio & Languages -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Professional Profile</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lawyer.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label">Professional Bio</label>
                            <textarea class="form-control" name="bio" rows="5" 
                                      placeholder="Tell clients about your experience, approach, and areas of expertise...">{{ $lawyer->bio }}</textarea>
                            <div class="form-text">This will be visible to clients when choosing a lawyer.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Languages Spoken</label>
                            <div class="row">
                                @php
                                    $allLanguages = ['English', 'Filipino', 'Cebuano', 'Ilocano', 'Bicolano', 'Waray', 'Hiligaynon', 'Pangasinan'];
                                    $lawyerLanguages = $lawyer->languages ?? [];
                                @endphp
                                @foreach($allLanguages as $language)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="languages[]" value="{{ $language }}" 
                                               id="lang_{{ Str::slug($language) }}"
                                               {{ in_array($language, $lawyerLanguages) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_{{ Str::slug($language) }}">
                                            {{ $language }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information (Read-only) -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Full Name</label>
                            <input type="text" class="form-control" value="{{ $lawyer->user->name }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <input type="text" class="form-control" value="{{ $lawyer->user->email }}" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Roll Number</label>
                            <input type="text" class="form-control" value="{{ $lawyer->roll_number ?? 'Not set' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">IBP Chapter</label>
                            <input type="text" class="form-control" value="{{ $lawyer->ibp_chapter ?? 'Not set' }}" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Years of Experience</label>
                            <input type="text" class="form-control" value="{{ $lawyer->years_experience ?? 0 }} years" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Account Status</label>
                            <input type="text" class="form-control text-success" value="Approved" disabled>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        To update account information or credentials, please contact the administrator.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
