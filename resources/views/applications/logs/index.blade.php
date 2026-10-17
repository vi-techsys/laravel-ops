@extends('layouts.app')

@section('title', $application->name . ' - Logs')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item active">Logs</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $application->name }} - Log Viewer</h1>
            <p class="text-muted">View and analyze application logs</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('applications.show', $application) }}" class="btn btn-secondary">
                ← Back to Application
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Log Statistics -->
    @if($statistics['success'] ?? false)
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ number_format($statistics['data']['total_entries'] ?? 0) }}</h3>
                        <small class="text-muted">Total Entries</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-danger mb-0">{{ number_format($statistics['data']['by_level']['ERROR'] ?? 0) }}</h3>
                        <small class="text-muted">Errors</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-warning mb-0">{{ number_format($statistics['data']['by_level']['WARNING'] ?? 0) }}</h3>
                        <small class="text-muted">Warnings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-info mb-0">{{ number_format($statistics['data']['by_level']['INFO'] ?? 0) }}</h3>
                        <small class="text-muted">Info</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-secondary mb-0">{{ number_format($statistics['data']['by_level']['DEBUG'] ?? 0) }}</h3>
                        <small class="text-muted">Debug</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ number_format(($statistics['data']['file_size'] ?? 0) / 1024, 1) }} KB</h3>
                        <small class="text-muted">File Size</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">🔍 Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('applications.logs.index', $application) }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="level" class="form-label">Log Level</label>
                        <select name="level" id="level" class="form-select">
                            <option value="">All Levels</option>
                            <option value="DEBUG" {{ $level === 'DEBUG' ? 'selected' : '' }}>Debug</option>
                            <option value="INFO" {{ $level === 'INFO' ? 'selected' : '' }}>Info</option>
                            <option value="NOTICE" {{ $level === 'NOTICE' ? 'selected' : '' }}>Notice</option>
                            <option value="WARNING" {{ $level === 'WARNING' ? 'selected' : '' }}>Warning</option>
                            <option value="ERROR" {{ $level === 'ERROR' ? 'selected' : '' }}>Error</option>
                            <option value="CRITICAL" {{ $level === 'CRITICAL' ? 'selected' : '' }}>Critical</option>
                            <option value="ALERT" {{ $level === 'ALERT' ? 'selected' : '' }}>Alert</option>
                            <option value="EMERGENCY" {{ $level === 'EMERGENCY' ? 'selected' : '' }}>Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="{{ $search }}" placeholder="Search logs...">
                    </div>
                    <div class="col-md-3">
                        <label for="limit" class="form-label">Limit</label>
                        <select name="limit" id="limit" class="form-select">
                            <option value="50" {{ $limit === 50 ? 'selected' : '' }}>50 entries</option>
                            <option value="100" {{ $limit === 100 ? 'selected' : '' }}>100 entries</option>
                            <option value="250" {{ $limit === 250 ? 'selected' : '' }}>250 entries</option>
                            <option value="500" {{ $limit === 500 ? 'selected' : '' }}>500 entries</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('applications.logs.index', $application) }}" class="btn btn-secondary">Clear Filters</a>
                        <form method="POST" action="{{ route('applications.logs.clear', $application) }}" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to clear all log files? This action cannot be undone.')">
                            @csrf
                            <button type="submit" class="btn btn-danger">🗑 Clear Logs</button>
                        </form>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Entries -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 Log Entries</h5>
            @if($logData['success'] ?? false)
                <span class="badge bg-secondary">
                    {{ $logData['data']['pagination']['total'] ?? 0 }} total entries
                </span>
            @endif
        </div>
        <div class="card-body">
            @if(!($logData['success'] ?? false))
                <div class="alert alert-danger">
                    <strong>Error:</strong> {{ $logData['error_message'] ?? $logData['error'] ?? 'Failed to load logs' }}
                </div>
            @elseif(empty($logData['data']['logs'] ?? []))
                <div class="alert alert-info">
                    No log entries found matching your filters.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Timestamp</th>
                                <th style="width: 100px;">Level</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logData['data']['logs'] as $log)
                                <tr class="{{ $log['level'] === 'ERROR' ? 'table-danger' : ($log['level'] === 'WARNING' ? 'table-warning' : '') }}">
                                    <td>
                                        <small class="text-muted">{{ $log['timestamp'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $levelClass = match($log['level'] ?? 'INFO') {
                                                'DEBUG' => 'secondary',
                                                'INFO' => 'info',
                                                'NOTICE' => 'primary',
                                                'WARNING' => 'warning',
                                                'ERROR' => 'danger',
                                                'CRITICAL' => 'danger',
                                                'ALERT' => 'danger',
                                                'EMERGENCY' => 'danger',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $levelClass }}">{{ $log['level'] ?? 'INFO' }}</span>
                                    </td>
                                    <td>
                                        <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word; font-size: 0.875rem;">{{ $log['message'] ?? '' }}</pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(($logData['data']['pagination']['has_more'] ?? false))
                    <nav aria-label="Log pagination">
                        <ul class="pagination justify-content-center mt-3">
                            @if($page > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ route('applications.logs.index', $application) }}?page={{ $page - 1 }}&limit={{ $limit }}&level={{ $level }}&search={{ $search }}">
                                        ← Previous
                                    </a>
                                </li>
                            @endif
                            <li class="page-item active">
                                <span class="page-link">Page {{ $page }}</span>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="{{ route('applications.logs.index', $application) }}?page={{ $page + 1 }}&limit={{ $limit }}&level={{ $level }}&search={{ $search }}">
                                    Next →
                                </a>
                            </li>
                        </ul>
                    </nav>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
