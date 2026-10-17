@extends('layouts.app')

@section('title', 'Register Application')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Register New Application</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('applications.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Application Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">A friendly name to identify this application</small>
                        </div>

                        <div class="mb-3">
                            <label for="base_url" class="form-label">Base URL <span class="text-danger">*</span></label>
                            <input id="base_url" type="url" class="form-control @error('base_url') is-invalid @enderror" 
                                   name="base_url" value="{{ old('base_url') }}" placeholder="https://myapp.example.com" required>
                            @error('base_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The root URL of your Laravel application (no trailing slash)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="environment" class="form-label">Environment <span class="text-danger">*</span></label>
                                <select id="environment" class="form-select @error('environment') is-invalid @enderror" 
                                        name="environment" required>
                                    <option value="">Select environment...</option>
                                    <option value="local" {{ old('environment') == 'local' ? 'selected' : '' }}>Local</option>
                                    <option value="development" {{ old('environment') == 'development' ? 'selected' : '' }}>Development</option>
                                    <option value="staging" {{ old('environment') == 'staging' ? 'selected' : '' }}>Staging</option>
                                    <option value="production" {{ old('environment') == 'production' ? 'selected' : '' }}>Production</option>
                                </select>
                                @error('environment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                                <select id="timezone" class="form-select @error('timezone') is-invalid @enderror" 
                                        name="timezone" required>
                                    <option value="">Select timezone...</option>
                                    <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ old('timezone') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                    <option value="Australia/Sydney" {{ old('timezone') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional description of this application</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="timeout_seconds" class="form-label">Timeout (seconds)</label>
                                <input id="timeout_seconds" type="number" class="form-control @error('timeout_seconds') is-invalid @enderror" 
                                       name="timeout_seconds" value="{{ old('timeout_seconds', 30) }}" min="5" max="120">
                                @error('timeout_seconds')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="max_retries" class="form-label">Max Retries</label>
                                <input id="max_retries" type="number" class="form-control @error('max_retries') is-invalid @enderror" 
                                       name="max_retries" value="{{ old('max_retries', 3) }}" min="0" max="10">
                                @error('max_retries')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">SSL Verification</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="verify_ssl" 
                                           name="verify_ssl" value="1" {{ old('verify_ssl', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="verify_ssl">
                                        Verify SSL certificates
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Security Note:</strong> The system will automatically generate secure API credentials 
                            after registration. You'll need to deploy the connector to the target application with these credentials.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Register Application
                            </button>
                            <a href="{{ route('applications.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
