@extends('layouts.admin')

@section('title', 'Add Lawyer')
@section('page-title', 'Add New Lawyer')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus me-2"></i>Lawyer Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.lawyers.store') }}">
                    @csrf
                    
                    <h6 class="text-muted mb-3">Account Details</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Professional Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">License Number <span class="text-danger">*</span></label>
                            <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" 
                                   value="{{ old('license_number') }}" required>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Years of Experience <span class="text-danger">*</span></label>
                            <input type="number" name="years_of_experience" class="form-control @error('years_of_experience') is-invalid @enderror" 
                                   value="{{ old('years_of_experience', 0) }}" min="0" required>
                            @error('years_of_experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Specializations <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($specializations as $spec)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="specializations[]" 
                                               value="{{ $spec->id }}" id="spec_{{ $spec->id }}"
                                               {{ in_array($spec->id, old('specializations', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="spec_{{ $spec->id }}">
                                            {{ $spec->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('specializations')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Primary Specialization <span class="text-danger">*</span></label>
                        <select name="primary_specialization" class="form-select @error('primary_specialization') is-invalid @enderror">
                            <option value="">Select primary specialization</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ old('primary_specialization') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('primary_specialization')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Languages <span class="text-danger">*</span></label>
                        <div class="row">
                            @php
                                $languages = ['Filipino', 'English', 'Bisaya', 'Cebuano', 'Tagalog', 'Hiligaynon'];
                            @endphp
                            @foreach($languages as $lang)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="languages[]" 
                                               value="{{ $lang }}" id="lang_{{ $lang }}"
                                               {{ in_array($lang, old('languages', ['Filipino', 'English'])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_{{ $lang }}">
                                            {{ $lang }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('languages')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Daily Appointments <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_appointments" class="form-control" 
                                   value="{{ old('max_daily_appointments', 8) }}" min="1" max="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Consultation Duration (mins) <span class="text-danger">*</span></label>
                            <input type="number" name="default_consultation_duration" class="form-control" 
                                   value="{{ old('default_consultation_duration', 60) }}" min="15" max="180" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="Brief biography...">{{ old('bio') }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Detailed description of expertise...">{{ old('description') }}</textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.lawyers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create Lawyer Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Information
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Creating a lawyer account will:
                </p>
                <ul class="text-muted">
                    <li>Create a user account with the "lawyer" role</li>
                    <li>Set the lawyer status to "approved" immediately</li>
                    <li>Create default working schedule (Mon-Fri, 8AM-5PM)</li>
                    <li>Allow the lawyer to start receiving appointments</li>
                </ul>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    <small>The lawyer can modify their schedule after logging in.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
