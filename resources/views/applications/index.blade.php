@extends('layouts.app')

@section('title', 'Applications')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">Managed Applications</h1>
            <p class="text-muted">Register and manage your Laravel applications</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('applications.create') }}" class="btn btn-primary btn-lg">
                + Register Application
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $stats['connected'] }}</h3>
                    <small class="text-muted">Connected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-0">{{ $stats['pending'] }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-secondary mb-0">{{ $stats['disconnected'] }}</h3>
                    <small class="text-muted">Disconnected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications List -->
    @if($applications->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Environment</th>
                            <th>Status</th>
                            <th>Last Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                            <tr>
                                <td>
                                    <a href="{{ route('applications.show', $app) }}" class="fw-bold text-decoration-none">
                                        {{ $app->name }}
                                    </a>
                                </td>
                                <td>
                                    <code class="small">{{ $app->base_url }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $app->environment_badge_class }}">
                                        {{ ucfirst($app->environment) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $app->status_badge_class }}">
                                        {{ ucfirst($app->connection_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($app->last_contact_at)
                                        <small>{{ $app->last_contact_at->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('applications.show', $app) }}" class="btn btn-outline-primary">
                                            View
                                        </a>
                                        <a href="{{ route('applications.edit', $app) }}" class="btn btn-outline-secondary">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $applications->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h4 class="text-muted mb-3">No applications registered yet</h4>
                <p class="text-muted mb-4">
                    Get started by registering your first Laravel application
                </p>
                <a href="{{ route('applications.create') }}" class="btn btn-primary btn-lg">
                    Register Your First Application
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
