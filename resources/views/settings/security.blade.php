@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h2 class="mb-4">Security Settings</h2>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Two-Factor Authentication -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🔐 Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6>Status: 
                                @if(Auth::user()->hasTwoFactorEnabled())
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </h6>
                            <p class="text-muted mb-0">
                                Add an extra layer of security by requiring a verification code from your email.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('two-factor.settings') }}" class="btn btn-primary">
                                Manage 2FA
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🖥️ Active Sessions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        You are currently logged in from the following devices. 
                        If you see any suspicious activity, you can log out from all other sessions.
                    </p>
                    <div class="alert alert-info">
                        <strong>Current Session:</strong> {{ request()->ip() }} | Last activity: Just now
                    </div>
                    <p class="text-muted small">
                        Note: Session management features can be expanded to show all active sessions 
                        and allow revoking specific sessions.
                    </p>
                </div>
            </div>

            <!-- Password -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">🔑 Password</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6>Last Updated: {{ Auth::user()->updated_at->diffForHumans() }}</h6>
                            <p class="text-muted mb-0">
                                Ensure your password is strong and unique to this site.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('password.confirm') }}" class="btn btn-warning">
                                Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Security Summary -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✅ Security Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Email Verified</span>
                            @if(Auth::user()->email_verified_at)
                                <span class="badge bg-success">✓ Verified</span>
                            @else
                                <span class="badge bg-warning">⚠ Not Verified</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Two-Factor Authentication</span>
                            @if(Auth::user()->hasTwoFactorEnabled())
                                <span class="badge bg-success">✓ Enabled</span>
                            @else
                                <span class="badge bg-secondary">✗ Disabled</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Account Status</span>
                            @if(Auth::user()->isLocked())
                                <span class="badge bg-danger">🔒 Locked</span>
                            @else
                                <span class="badge bg-success">✓ Active</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Failed Login Attempts</span>
                            <span class="badge bg-{{ Auth::user()->login_attempts > 0 ? 'warning' : 'success' }}">
                                {{ Auth::user()->login_attempts }} / 5
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
