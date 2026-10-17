@extends('layouts.app')

@section('title', 'Two-Factor Authentication Settings')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Two-Factor Authentication Settings</h4>
                </div>
                <div class="card-body p-4">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>Current Status: 
                            @if($enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </h5>
                        
                        <p class="text-muted mt-2">
                            Two-factor authentication adds an extra layer of security to your account 
                            by requiring a verification code from your email in addition to your password.
                        </p>
                    </div>

                    @if($enabled)
                        <div class="alert alert-info">
                            <strong>Note:</strong> When you log in, you'll be asked to enter a 6-digit 
                            verification code sent to your email address ({{ $user->email }}).
                        </div>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')

                            <div class="mb-3">
                                <label for="password" class="form-label">Confirm Your Password</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Enter your password to disable two-factor authentication.</small>
                            </div>

                            <button type="submit" class="btn btn-danger">
                                Disable Two-Factor Authentication
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <strong>Recommendation:</strong> Enable two-factor authentication for enhanced security.
                        </div>

                        <form method="POST" action="{{ route('two-factor.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Enable Two-Factor Authentication
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Security Information</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Verification codes are sent to your registered email address</li>
                        <li>Codes expire after 10 minutes</li>
                        <li>After 5 failed login attempts, your account will be locked for 15 minutes</li>
                        <li>You can disable 2FA at any time by confirming your password</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
