@extends('layouts.app')

@section('title', 'Security Audit Log')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Security Audit Log</h2>
                <a href="{{ route('two-factor.settings') }}" class="btn btn-secondary">
                    Back to Security Settings
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Recent Security Events</h5>
                </div>
                <div class="card-body p-0">
                    @if($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event</th>
                                        <th>IP Address</th>
                                        <th>Details</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>
                                                @php
                                                    $eventIcons = [
                                                        'login.success' => ['icon' => '✅', 'label' => 'Login Successful', 'class' => 'success'],
                                                        'login.failed' => ['icon' => '❌', 'label' => 'Login Failed', 'class' => 'danger'],
                                                        'logout' => ['icon' => '🚪', 'label' => 'Logout', 'class' => 'secondary'],
                                                        'two_factor.code_sent' => ['icon' => '📧', 'label' => '2FA Code Sent', 'class' => 'info'],
                                                        'two_factor.enabled' => ['icon' => '🔐', 'label' => '2FA Enabled', 'class' => 'success'],
                                                        'two_factor.disabled' => ['icon' => '🔓', 'label' => '2FA Disabled', 'class' => 'warning'],
                                                        'two_factor.verified' => ['icon' => '✓', 'label' => '2FA Verified', 'class' => 'success'],
                                                        'account.locked' => ['icon' => '🔒', 'label' => 'Account Locked', 'class' => 'danger'],
                                                        'password.changed' => ['icon' => '🔑', 'label' => 'Password Changed', 'class' => 'info'],
                                                        'security_settings.changed' => ['icon' => '⚙️', 'label' => 'Settings Changed', 'class' => 'info'],
                                                    ];
                                                    $eventData = $eventIcons[$log->event] ?? ['icon' => '📝', 'label' => $log->event, 'class' => 'secondary'];
                                                @endphp
                                                <span class="badge bg-{{ $eventData['class'] }}">
                                                    {{ $eventData['icon'] }} {{ $eventData['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <code>{{ $log->ip_address ?? 'N/A' }}</code>
                                            </td>
                                            <td>
                                                @if($log->metadata)
                                                    @if(isset($log->metadata['email']))
                                                        <small class="text-muted">{{ $log->metadata['email'] }}</small>
                                                    @endif
                                                    @if(isset($log->metadata['attempts']))
                                                        <br><small class="text-muted">Attempts: {{ $log->metadata['attempts'] }}</small>
                                                    @endif
                                                    @if(isset($log->metadata['duration_minutes']))
                                                        <br><small class="text-muted">Duration: {{ $log->metadata['duration_minutes'] }} min</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                                <br>
                                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">No security events recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6>About Security Audit Log</h6>
                    <p class="text-muted small mb-0">
                        This log tracks all security-related events for your account, including login attempts, 
                        password changes, two-factor authentication events, and account lockouts. 
                        If you notice any suspicious activity, please change your password immediately and 
                        enable two-factor authentication if not already enabled.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
