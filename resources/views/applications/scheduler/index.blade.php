@extends('layouts.app')

@section('title', $application->name . ' - Scheduler Monitor')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item active">Scheduler Monitor</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $application->name }} - Scheduler Monitor</h1>
            <p class="text-muted">Monitor and manage scheduled tasks</p>
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

    <!-- Scheduler Statistics -->
    @if($scheduleData['success'] ?? false)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ $scheduleData['data']['total'] ?? 0 }}</h3>
                        <small class="text-muted">Scheduled Tasks</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ $scheduleData['data']['cron_configured'] ? 'Yes' : 'No' }}</h3>
                        <small class="text-muted">Cron Configured</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold mb-0">{{ count($history['data']['history'] ?? []) }}</h3>
                        <small class="text-muted">Recent Executions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Tasks -->
        @if(!empty($scheduleData['data']['tasks']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Scheduled Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Command</th>
                                    <th>Schedule</th>
                                    <th>Timezone</th>
                                    <th>Next Run</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduleData['data']['tasks'] as $task)
                                    <tr>
                                        <td>
                                            <code>{{ $task['command'] }}</code>
                                            @if($task['description'] && $task['description'] !== 'No description')
                                                <br><small class="text-muted">{{ $task['description'] }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info">{{ $task['expression'] }}</span></td>
                                        <td><small>{{ $task['timezone'] }}</small></td>
                                        <td><small>{{ $task['next_run'] ?? 'N/A' }}</small></td>
                                        <td>
                                            <form method="POST" action="{{ route('applications.scheduler.run-task', $application) }}" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to manually run this task?')">
                                                @csrf
                                                <input type="hidden" name="command" value="{{ $task['command'] }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Run Now
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                No scheduled tasks found.
            </div>
        @endif
    @else
        <div class="alert alert-danger">
            <strong>Error:</strong> {{ $scheduleData['error'] ?? $scheduleData['message'] ?? 'Failed to load schedule data' }}
        </div>
    @endif

    <!-- Execution History -->
    @if($history['success'] ?? false)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Execution History</h5>
            </div>
            <div class="card-body">
                @if(empty($history['data']['history']))
                    <div class="alert alert-info mb-0">
                        {{ $history['data']['message'] ?? 'No execution history available.' }}
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Executed At</th>
                                    <th>Command</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history['data']['history'] as $entry)
                                    <tr>
                                        <td><small>{{ $entry->executed_at ?? 'N/A' }}</small></td>
                                        <td><code>{{ $entry->command ?? 'N/A' }}</code></td>
                                        <td>
                                            <span class="badge bg-{{ ($entry->status ?? 'success') === 'success' ? 'success' : 'danger' }}">
                                                {{ ucfirst($entry->status ?? 'success') }}
                                            </span>
                                        </td>
                                        <td><small>{{ $entry->duration ?? 'N/A' }}ms</small></td>
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
