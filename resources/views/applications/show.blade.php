@extends('layouts.app')

@section('title', $application->name)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item active">{{ $application->name }}</li>
                </ol>
            </nav>
            <h1 class="fw-bold">{{ $application->name }}</h1>
            <p class="text-muted mb-0">{{ $application->base_url }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('applications.edit', $application) }}" class="btn btn-outline-primary">
                Edit
            </a>
            <form method="POST" action="{{ route('applications.destroy', $application) }}" class="d-inline" 
                  onsubmit="return confirm('Are you sure you want to delete this application?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>

    <!-- Status and Info -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Connection Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $application->status_badge_class }} fs-6">
                            {{ ucfirst($application->connection_status) }}
                        </span>
                    </div>
                    
                    @if($application->connector_version)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Connector Version:</span>
                            <span>{{ $application->connector_version }}</span>
                        </div>
                    @endif

                    @if($application->last_contact_at)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Last Contact:</span>
                            <span>{{ $application->last_contact_at->diffForHumans() }}</span>
                        </div>
                    @endif

                    @if($application->last_health_check_at)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Last Health Check:</span>
                            <span>{{ $application->last_health_check_at->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Application Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Environment:</span>
                        <span class="badge bg-{{ $application->environment_badge_class }}">
                            {{ ucfirst($application->environment) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Timezone:</span>
                        <span>{{ $application->timezone }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Timeout:</span>
                        <span>{{ $application->timeout_seconds }}s</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">SSL Verification:</span>
                        <span>
                            @if($application->verify_ssl)
                                <span class="text-success">✓ Enabled</span>
                            @else
                                <span class="text-warning">✗ Disabled</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($application->description)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Description</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $application->description }}</p>
            </div>
        </div>
    @endif

    <!-- Credentials Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">API Credentials</h5>
            <form method="POST" action="{{ route('applications.regenerate-credentials', $application) }}"
                  onsubmit="return confirm('This will invalidate the current credentials. Continue?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning">
                    Regenerate Credentials
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <strong>⚠ Important:</strong> These credentials are used to authenticate requests between Laravel Ops 
                and the managed application. Keep them secure and never share them publicly.
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">API Key</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" value="{{ $application->api_key }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $application->api_key }}')">
                        Copy
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">API Secret</label>
                <div class="input-group">
                    <input type="password" class="form-control font-monospace" id="apiSecret" value="{{ $application->api_secret }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret('apiSecret')">
                        Show/Hide
                    </button>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $application->api_secret }}')">
                        Copy
                    </button>
                </div>
            </div>

            @if($application->webhook_secret)
                <div class="mb-3">
                    <label class="form-label fw-bold">Webhook Secret</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="webhookSecret" value="{{ $application->webhook_secret }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret('webhookSecret')">
                            Show/Hide
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $application->webhook_secret }}')">
                            Copy
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if($application->isConnected())
                    <div class="col-md-2">
                        <a href="{{ route('applications.operations.index', $application) }}" class="btn btn-primary w-100">
                            ⚡ Operations
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('applications.logs.index', $application) }}" class="btn btn-info w-100">
                             Logs
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('applications.queue.index', $application) }}" class="btn btn-warning w-100">
                            📨 Queue
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('applications.scheduler.index', $application) }}" class="btn btn-success w-100">
                            ⏰ Scheduler
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('applications.health.index', $application) }}" class="btn btn-success w-100">
                            ❤️ Health
                        </a>
                    </div>
                @endif

                @if(!$application->isRevoked())
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('applications.revoke', $application) }}"
                              onsubmit="return confirm('This will permanently revoke this application. It will no longer be able to connect. Continue?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                Revoke Application
                            </button>
                        </form>
                    </div>
                @endif

                <div class="col-md-2">
                    <a href="{{ route('applications.edit', $application) }}" class="btn btn-outline-primary w-100">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleSecret(elementId) {
    const input = document.getElementById(elementId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>
@endpush
@endsection
