@extends('layouts.app')

@section('title', 'Operation Result')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.operations.index', $application) }}">Operations</a></li>
            <li class="breadcrumb-item active">Result #{{ $operation->id }}</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">Operation Result</h1>
            <p class="text-muted">Executed {{ $operation->created_at->diffForHumans() }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('applications.operations.index', $application) }}" class="btn btn-secondary">
                Back to Operations
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
                        <span class="badge bg-{{ $operation->status_badge_class }} fs-5">
                            {{ $operation->status_icon }} {{ ucfirst($operation->status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Operation:</label>
                        <code>{{ $operation->operation }}</code>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Initiated By:</label>
                        <span>{{ $operation->user->name ?? 'System' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold">Duration:</label>
                        <span>{{ $operation->duration_ms ?? 'N/A' }}ms</span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Started At:</label>
                        <span>{{ $operation->started_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Completed At:</label>
                        <span>{{ $operation->completed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($operation->requires_confirmation)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Confirmation</h5>
            </div>
            <div class="card-body">
                <div class="mb-0">
                    <label class="fw-bold">Confirmed:</label>
                    @if($operation->confirmed)
                        <span class="text-success">✓ Yes</span>
                        <small class="text-muted ms-2">(at {{ $operation->confirmed_at->format('Y-m-d H:i:s') }})</small>
                    @else
                        <span class="text-danger">✗ No</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($operation->parameters && count($operation->parameters) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Parameters</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode($operation->parameters, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    @if($operation->isSucceeded() && $operation->result)
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Result</h5>
                <button class="btn btn-sm btn-light" onclick="copyResultToClipboard()">
                    📋 Copy
                </button>
            </div>
            <div class="card-body">
                @php
                    $result = $operation->result;
                    $resultJson = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                @endphp
                
                @if(is_array($result))
                    <pre id="resultContent" class="bg-light p-3 rounded mb-0" style="max-height: 500px; overflow-y: auto; font-size: 0.875rem;"><code class="language-json">{{ $resultJson }}</code></pre>
                @else
                    <pre id="resultContent" class="bg-light p-3 rounded mb-0" style="max-height: 500px; overflow-y: auto;"><code>{{ $result }}</code></pre>
                @endif
            </div>
        </div>
    @endif

    @if($operation->isFailed())
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Error Details</h5>
            </div>
            <div class="card-body">
                @if($operation->error_type)
                    <div class="mb-3">
                        <label class="fw-bold">Error Type:</label>
                        <code>{{ $operation->error_type }}</code>
                    </div>
                @endif
                @if($operation->error_message)
                    <div class="mb-0">
                        <label class="fw-bold">Error Message:</label>
                        <pre class="bg-light p-3 rounded"><code>{{ $operation->error_message }}</code></pre>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($operation->logs->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Operation Logs</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Logged At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operation->logs as $log)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $log->level_badge_class }}">{{ $log->level }}</span>
                                </td>
                                <td>{{ $log->message }}</td>
                                <td><small>{{ $log->logged_at->format('H:i:s') }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@push('scripts')
<script>
// Simple JSON syntax highlighter
function syntaxHighlight(json) {
    if (typeof json != 'string') {
        json = JSON.stringify(json, undefined, 2);
    }
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

// Copy result to clipboard
function copyResultToClipboard() {
    const resultElement = document.getElementById('resultContent');
    if (resultElement) {
        const text = resultElement.innerText;
        navigator.clipboard.writeText(text).then(() => {
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
}

// Apply syntax highlighting to JSON code blocks
document.addEventListener('DOMContentLoaded', function() {
    const jsonBlocks = document.querySelectorAll('code.language-json');
    jsonBlocks.forEach(block => {
        try {
            const json = JSON.parse(block.textContent);
            block.innerHTML = syntaxHighlight(json);
        } catch (e) {
            // If parsing fails, leave as is
        }
    });
});
</script>

<style>
pre code .key { color: #d73a49; font-weight: bold; }
pre code .string { color: #032f62; }
pre code .number { color: #005cc5; }
pre code .boolean { color: #005cc5; font-weight: bold; }
pre code .null { color: #6f42c1; font-weight: bold; }
</style>
@endpush
@endsection
