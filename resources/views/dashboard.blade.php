@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Hero Section -->
    <div class="bg-primary text-white py-5 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Welcome back, {{ Auth::user()->name }}!</h1>
                    <p class="lead mb-4">
                        Monitor, diagnose, and manage your Laravel applications from one central dashboard.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('applications.create') }}" class="btn btn-light btn-lg">
                            + Register Application
                        </a>
                        <a href="{{ route('applications.index') }}" class="btn btn-outline-light btn-lg">
                            View All Applications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <!-- Status Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="{{ route('applications.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase small mb-2">Total Applications</h6>
                                    <h2 class="fw-bold mb-0">{{ $stats['total'] }}</h2>
                                    <small class="text-muted">Registered targets</small>
                                </div>
                                <div class="fs-1 text-primary opacity-25">📊</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('applications.index') }}?status=connected" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase small mb-2">Connected</h6>
                                    <h2 class="fw-bold mb-0 text-success">{{ $stats['connected'] }}</h2>
                                    <small class="text-muted">Healthy connections</small>
                                </div>
                                <div class="fs-1 text-success opacity-25">✓</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('applications.index') }}?status=pending" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase small mb-2">Needs Attention</h6>
                                    <h2 class="fw-bold mb-0 text-warning">{{ $stats['pending'] + $stats['disconnected'] }}</h2>
                                    <small class="text-muted">Pending or disconnected</small>
                                </div>
                                <div class="fs-1 text-warning opacity-25">⚠</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity and Attention Needed -->
        <div class="row g-4">
            <!-- Recent Health Checks -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Activity</h5>
                        <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @if($recentHealthChecks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Application</th>
                                            <th>Status</th>
                                            <th>Checked</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentHealthChecks as $check)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('applications.show', $check->managedApplication) }}" class="fw-bold text-decoration-none">
                                                        {{ $check->managedApplication->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $check->status_badge_class }}">
                                                        {{ ucfirst($check->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $check->checked_at->diffForHumans() }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="text-muted mb-2">No recent health checks</div>
                                <small class="text-muted">Connect an application to see activity here</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Applications Needing Attention -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Needs Attention</h5>
                    </div>
                    <div class="card-body">
                        @if($applicationsNeedingAttention->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($applicationsNeedingAttention as $app)
                                    <a href="{{ route('applications.show', $app) }}" class="list-group-item list-group-item-action d-flex align-items-center px-0">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $app->name }}</div>
                                            <small class="text-muted">{{ $app->base_url }}</small>
                                        </div>
                                        <span class="badge bg-{{ $app->status_badge_class }}">
                                            {{ ucfirst($app->connection_status) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="text-success fs-1 mb-2">✓</div>
                                <div class="text-muted">All applications are healthy!</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if($stats['total'] == 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-5 text-center">
                            <h3 class="fw-bold mb-3">Get Started</h3>
                            <p class="text-muted mb-4">
                                Register your first Laravel application to begin centralized management
                            </p>
                            <a href="{{ route('applications.create') }}" class="btn btn-primary btn-lg">
                                + Register Your First Application
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
