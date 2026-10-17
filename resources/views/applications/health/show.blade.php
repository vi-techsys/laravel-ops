@extends('layouts.app')

@section('title', 'Health Check Details')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.health.index', $application) }}">Health History</a></li>
            <li class="breadcrumb-item active">Check #{{ $healthCheck->id }}</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">Health Check Details</h1>
            <p class="text-muted">Check performed {{ $healthCheck->checked_at->diffForHumans() }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('applications.health.index', $application) }}" class="btn btn-secondary">
                Back to History
            </a>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Status Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold">Status:</label>
                        <span class="badge bg-{{ $healthCheck->status_badge_class }} fs-5">
                            {{ $healthCheck->status_icon }} {{ ucfirst($healthCheck->status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Message:</label>
                        <p class="mb-0">{{ $healthCheck->message }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold">Response Time:</label>
                        <span>{{ $healthCheck->response_time_ms ?? 'N/A' }}ms</span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Checked By:</label>
                        <span class="badge bg-secondary">{{ $healthCheck->checked_by }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Checked At:</label>
                        <span>{{ $healthCheck->checked_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($healthCheck->connector_version)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Connector Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-0">
                    <label class="fw-bold">Connector Version:</label>
                    <code>{{ $healthCheck->connector_version }}</code>
                </div>
            </div>
        </div>
    @endif

    @if($healthCheck->checks && count($healthCheck->checks) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Component Status</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($healthCheck->checks as $component => $check)
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title text-uppercase small text-muted">{{ $component }}</h6>
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $check['status'] === 'ok' ? 'success' : 'danger' }}">
                                            {{ $check['status'] === 'ok' ? '✓' : '✗' }} {{ ucfirst($check['status']) }}
                                        </span>
                                    </div>
                                    <p class="card-text small mb-0">{{ $check['message'] ?? '' }}</p>
                                    @if(isset($check['driver']))
                                        <small class="text-muted">Driver: {{ $check['driver'] }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($healthCheck->error_type || $healthCheck->error_message)
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Error Details</h5>
            </div>
            <div class="card-body">
                @if($healthCheck->error_type)
                    <div class="mb-3">
                        <label class="fw-bold">Error Type:</label>
                        <code>{{ $healthCheck->error_type }}</code>
                    </div>
                @endif
                @if($healthCheck->error_message)
                    <div class="mb-0">
                        <label class="fw-bold">Error Message:</label>
                        <pre class="bg-light p-3 rounded"><code>{{ $healthCheck->error_message }}</code></pre>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
