@extends('layouts.app')

@section('title', $application->name . ' - Operations')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item active">Operations</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $application->name }} - Operations</h1>
            <p class="text-muted">Execute approved operations on this application</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-0">{{ $statistics['total'] }}</h3>
                    <small class="text-muted">Total (24h)</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $statistics['succeeded'] }}</h3>
                    <small class="text-muted">Succeeded</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $statistics['failed'] }}</h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $statistics['success_rate'] }}%</h3>
                    <small class="text-muted">Success Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-0">{{ $statistics['avg_duration'] }}ms</h3>
                    <small class="text-muted">Avg Duration</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Operations -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Available Operations</h5>
        </div>
        <div class="card-body">
            @php
                // Get all unique categories from operations
                $categories = array_unique(array_column($operations, 'category'));
                sort($categories);
            @endphp
            
            @foreach($categories as $category)
                @php
                    $categoryOps = array_filter($operations, fn($op) => ($op['category'] ?? 'other') === $category);
                @endphp
                @if(count($categoryOps) > 0)
                    <h6 class="text-uppercase text-muted small mb-3">{{ ucfirst($category) }}</h6>
                    <div class="row g-3 mb-4">
                        @foreach($categoryOps as $opName => $op)
                            <div class="col-md-6">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            {{ $op['name'] }}
                                            @if(($op['risk_level'] ?? 'medium') === 'none')
                                                <span class="badge bg-success">Safe</span>
                                            @elseif(($op['risk_level'] ?? 'medium') === 'low')
                                                <span class="badge bg-info">Low Risk</span>
                                            @elseif(($op['risk_level'] ?? 'medium') === 'medium')
                                                <span class="badge bg-warning">Medium Risk</span>
                                            @else
                                                <span class="badge bg-danger">High Risk</span>
                                            @endif
                                        </h6>
                                        <p class="card-text small text-muted">{{ $op['description'] }}</p>
                                        <a href="{{ route('applications.operations.create', [$application, $opName]) }}" class="btn btn-sm btn-primary">
                                            Execute
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Operation History -->
    @if($history->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Operations</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>Operation</th>
                            <th>Initiated By</th>
                            <th>Duration</th>
                            <th>Executed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $op)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $op->status_badge_class }}">
                                        {{ $op->status_icon }} {{ ucfirst($op->status) }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $op->operation }}</code>
                                </td>
                                <td>
                                    @if($op->user)
                                        {{ $op->user->name }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($op->duration_ms)
                                        {{ $op->duration_ms }}ms
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $op->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('applications.operations.show', [$application, $op]) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h4 class="text-muted mb-3">No operations executed yet</h4>
                <p class="text-muted">
                    Select an operation above to get started
                </p>
            </div>
        </div>
    @endif
</div>
@endsection
