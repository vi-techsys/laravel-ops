@extends('layouts.app')

@section('title', $application->name . ' - Health History')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item active">Health History</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $application->name }} - Health History</h1>
            <p class="text-muted">{{ $application->base_url }}</p>
        </div>
        <div class="col-md-4 text-end">
            <form method="POST" action="{{ route('applications.health.check', $application) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    Run Health Check
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-0">{{ $statistics['total'] }}</h3>
                    <small class="text-muted">Total Checks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $statistics['healthy'] }}</h3>
                    <small class="text-muted">Healthy</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-0">{{ $statistics['degraded'] }}</h3>
                    <small class="text-muted">Degraded</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $statistics['unreachable'] }}</h3>
                    <small class="text-muted">Unreachable</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $statistics['uptime_percentage'] }}%</h3>
                    <small class="text-muted">Uptime</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-0">{{ $statistics['avg_response_time'] }}ms</h3>
                    <small class="text-muted">Avg Response</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Checks Table -->
    @if($healthChecks->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Response Time</th>
                            <th>Connector Version</th>
                            <th>Checked By</th>
                            <th>Checked At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($healthChecks as $check)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $check->status_badge_class }}">
                                        {{ $check->status_icon }} {{ ucfirst($check->status) }}
                                    </span>
                                </td>
                                <td>{{ $check->message }}</td>
                                <td>
                                    @if($check->response_time_ms)
                                        {{ $check->response_time_ms }}ms
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($check->connector_version)
                                        <code>{{ $check->connector_version }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $check->checked_by }}</span>
                                </td>
                                <td>
                                    <small>{{ $check->checked_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('applications.health.show', [$application, $check]) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $healthChecks->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h4 class="text-muted mb-3">No health checks recorded yet</h4>
                <p class="text-muted mb-4">
                    Run your first health check to start monitoring this application
                </p>
                <form method="POST" action="{{ route('applications.health.check', $application) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">
                        Run First Health Check
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
