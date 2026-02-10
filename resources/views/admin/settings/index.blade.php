@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        @foreach($settings as $group => $items)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                        id="{{ $group }}-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#{{ $group }}" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="{{ $group }}" 
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ ucfirst($group) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        @foreach($settings as $group => $items)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                 id="{{ $group }}" 
                                 role="tabpanel" 
                                 aria-labelledby="{{ $group }}-tab">
                                
                                <h5 class="card-title mb-4 border-bottom pb-2">{{ ucfirst($group) }} Settings</h5>
                                
                                <div class="row g-4">
                                    @foreach($items as $setting)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="{{ $setting->key }}" class="form-label d-flex align-items-center">
                                                    {{ $setting->label }}
                                                    @if($setting->description)
                                                        <i class="bi bi-info-circle ms-2 text-muted" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="{{ $setting->description }}">
                                                        </i>
                                                    @endif
                                                </label>
                                                
                                                @switch($setting->type)
                                                    @case('number')
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="{{ $setting->key }}" 
                                                               name="{{ $setting->key }}" 
                                                               value="{{ old($setting->key, $setting->value) }}">
                                                        @break
                                                        
                                                    @case('boolean')
                                                        <select class="form-select" 
                                                                id="{{ $setting->key }}" 
                                                                name="{{ $setting->key }}">
                                                            <option value="1" {{ old($setting->key, $setting->value) == '1' ? 'selected' : '' }}>Yes (True)</option>
                                                            <option value="0" {{ old($setting->key, $setting->value) == '0' ? 'selected' : '' }}>No (False)</option>
                                                        </select>
                                                        @break
                                                        
                                                    @case('textarea')
                                                        <textarea class="form-control" 
                                                                  id="{{ $setting->key }}" 
                                                                  name="{{ $setting->key }}" 
                                                                  rows="3">{{ old($setting->key, $setting->value) }}</textarea>
                                                        @break
                                                        
                                                    @case('json')
                                                        <textarea class="form-control font-monospace" 
                                                                  id="{{ $setting->key }}" 
                                                                  name="{{ $setting->key }}" 
                                                                  rows="5">{{ old($setting->key, $setting->value) }}</textarea>
                                                        <small class="text-muted">Enter valid JSON format only.</small>
                                                        @break
                                                        
                                                    @default
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="{{ $setting->key }}" 
                                                               name="{{ $setting->key }}" 
                                                               value="{{ old($setting->key, $setting->value) }}">
                                                @endswitch
                                                
                                                @if($setting->description)
                                                    <div class="form-text">{{ $setting->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="card-footer d-flex justify-content-end p-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
@endsection
