@extends('layouts.app')

@section('title', $application->name . ' - Queue Monitor')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item active">Queue Monitor</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $application->name }} - Queue Monitor</h1>
            <p class="text-muted">Monitor and manage application queues</p>
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

    <!-- Queue Statistics -->
    @if($queueData['success'] ?? false)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ $queueData['data']['driver'] ?? 'N/A' }}</h3>
                        <small class="text-muted">Queue Driver</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-primary mb-0">{{ array_sum(array_column($queueData['data']['queues'] ?? [], 'size')) }}</h3>
                        <small class="text-muted">Pending Jobs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-danger mb-0">{{ $queueData['data']['failed_count'] ?? 0 }}</h3>
                        <small class="text-muted">Failed Jobs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="mb-0">{{ $queueData['data']['worker_status'] ?? 'N/A' }}</h6>
                        <small class="text-muted">Worker Status</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Sizes -->
        @if(!empty($queueData['data']['queues']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Queue Sizes</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($queueData['data']['queues'] as $queueName => $queueInfo)
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h4 class="fw-bold mb-0">{{ $queueInfo['size'] }}</h4>
                                        <small class="text-muted">{{ ucfirst($queueName) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="alert alert-danger">
            <strong>Error:</strong> {{ $queueData['error'] ?? $queueData['message'] ?? 'Failed to load queue data' }}
        </div>
    @endif

    <!-- Failed Jobs -->
    @if($failedJobs['success'] ?? false)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Failed Jobs ({{ $failedJobs['data']['total'] ?? 0 }})</h5>
                <div>
                    <form method="POST" action="{{ route('applications.queue.retry-all', $application) }}" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to retry all {{ $failedJobs['data']['total'] ?? 0 }} failed jobs?')">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            Retry All Failed
                        </button>
                    </form>
                    <form method="POST" action="{{ route('applications.queue.purge', $application) }}" class="d-inline ms-2"
                          onsubmit="return confirm('Are you sure you want to purge all failed jobs? This cannot be undone.')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            Purge Failed
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @if(empty($failedJobs['data']['jobs']))
                    <div class="alert alert-success mb-0">
                        No failed jobs found!
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Queue</th>
                                    <th>Failed At</th>
                                    <th>Exception Summary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failedJobs['data']['jobs'] as $job)
                                    <tr>
                                        <td>{{ $job['id'] }}</td>
                                        <td><span class="badge bg-secondary">{{ $job['queue'] ?? 'default' }}</span></td>
                                        <td><small>{{ $job['failed_at'] ?? 'N/A' }}</small></td>
                                        <td>
                                            <small class="text-danger">
                                                {{ Str::limit($job['exception_summary'] ?? 'Unknown error', 100) }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
