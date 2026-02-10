@extends('layouts.admin')

@section('title', 'Edit Lawyer')
@section('page-title', 'Edit: ' . $lawyer->user->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2"></i>Edit Lawyer Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.lawyers.update', $lawyer) }}">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-muted mb-3">Account Details</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $lawyer->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $lawyer->user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="pending" {{ old('status', $lawyer->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('status', $lawyer->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="suspended" {{ old('status', $lawyer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="rejected" {{ old('status', $lawyer->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Professional Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">License Number <span class="text-danger">*</span></label>
                            <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" 
                                   value="{{ old('license_number', $lawyer->license_number) }}" required>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Years of Experience <span class="text-danger">*</span></label>
                            <input type="number" name="years_of_experience" class="form-control @error('years_of_experience') is-invalid @enderror" 
                                   value="{{ old('years_of_experience', $lawyer->years_of_experience) }}" min="0" required>
                            @error('years_of_experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Specializations <span class="text-danger">*</span></label>
                        @php
                            $currentSpecs = old('specializations', $lawyer->specializations->pluck('id')->toArray());
                        @endphp
                        <div class="row">
                            @foreach($specializations as $spec)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="specializations[]" 
                                               value="{{ $spec->id }}" id="spec_{{ $spec->id }}"
                                               {{ in_array($spec->id, $currentSpecs) ? 'checked' : '' }}>
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
                        @php
                            $primarySpec = $lawyer->specializations->where('pivot.is_primary', true)->first();
                        @endphp
                        <label class="form-label">Primary Specialization <span class="text-danger">*</span></label>
                        <select name="primary_specialization" class="form-select @error('primary_specialization') is-invalid @enderror">
                            <option value="">Select primary specialization</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ old('primary_specialization', $primarySpec?->id) == $spec->id ? 'selected' : '' }}>
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
                        @php
                            $languages = ['Filipino', 'English', 'Bisaya', 'Cebuano', 'Tagalog', 'Hiligaynon'];
                            $currentLangs = old('languages', $lawyer->languages ?? []);
                        @endphp
                        <div class="row">
                            @foreach($languages as $lang)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="languages[]" 
                                               value="{{ $lang }}" id="lang_{{ $lang }}"
                                               {{ in_array($lang, $currentLangs) ? 'checked' : '' }}>
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
                                   value="{{ old('max_daily_appointments', $lawyer->max_daily_appointments) }}" min="1" max="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Consultation Duration (mins) <span class="text-danger">*</span></label>
                            <input type="number" name="default_consultation_duration" class="form-control" 
                                   value="{{ old('default_consultation_duration', $lawyer->default_consultation_duration) }}" min="15" max="180" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3">{{ old('bio', $lawyer->bio) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $lawyer->description) }}</textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.lawyers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Lawyer
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
                <p class="text-muted small">
                    <strong>Created:</strong> {{ $lawyer->created_at->format('M j, Y g:i A') }}
                </p>
                @if($lawyer->approved_at)
                    <p class="text-muted small">
                        <strong>Approved:</strong> {{ $lawyer->approved_at->format('M j, Y g:i A') }}
                    </p>
                @endif
                <p class="text-muted small mb-0">
                    <strong>Last Updated:</strong> {{ $lawyer->updated_at->format('M j, Y g:i A') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
