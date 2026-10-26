@extends('layouts.app')

@section('title', 'Execute Operation')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('applications.index') }}">Applications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.show', $application) }}">{{ $application->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applications.operations.index', $application) }}">Operations</a></li>
            <li class="breadcrumb-item active">Execute</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">Execute Operation</h1>
            <p class="text-muted">{{ $application->name }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        @if($operationDetails)
                            {{ $operationDetails['name'] }}
                        @else
                            Select Operation
                        @endif
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('applications.operations.store', $application) }}">
                        @csrf

                        @if(!$operationDetails)
                            <!-- Operation Selection -->
                            <div class="mb-3">
                                <label for="operation" class="form-label">Select Operation <span class="text-danger">*</span></label>
                                <select id="operation" class="form-select @error('operation') is-invalid @enderror" name="operation" required>
                                    <option value="">Choose an operation...</option>
                                    @foreach($availableOperations as $opName => $op)
                                        <option value="{{ $opName }}" {{ old('operation') == $opName ? 'selected' : '' }}>
                                            {{ $op['name'] }} 
                                            @if(($op['risk_level'] ?? 'medium') === 'high')
                                                ️ HIGH RISK
                                            @elseif(($op['risk_level'] ?? 'medium') === 'medium')
                                                ⚠️
                                            @endif
                                            ({{ $op['category'] ?? 'other' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('operation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <input type="hidden" name="operation" value="{{ request()->route('operation') }}">
                            
                            <!-- Risk Level Warning -->
                            @php
                                $riskLevel = $operationDetails['risk_level'] ?? 'medium';
                                $requiresConfirmation = $operationDetails['requires_confirmation'] ?? false;
                            @endphp
                            
                            @if($riskLevel === 'high')
                                <div class="alert alert-danger">
                                    <h5 class="alert-heading">⚠️ HIGH RISK OPERATION</h5>
                                    <p class="mb-2"><strong>{{ $operationDetails['name'] }}</strong></p>
                                    <p class="mb-2">{{ $operationDetails['description'] }}</p>
                                    <hr>
                                    <p class="mb-0"><strong>This operation can cause significant changes to your application. Please proceed with caution.</strong></p>
                                </div>
                            @elseif($riskLevel === 'medium')
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">⚠️ Medium Risk Operation</h6>
                                    <p class="mb-0">{{ $operationDetails['description'] }}</p>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <p class="mb-0">{{ $operationDetails['description'] }}</p>
                                </div>
                            @endif

                            @if($requiresConfirmation)
                                <div class="alert alert-danger border-danger">
                                    <h6 class="alert-heading"><strong>🔒 Confirmation Required</strong></h6>
                                    <p class="mb-2">This operation requires explicit confirmation before execution.</p>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" class="form-check-input" id="confirmed" name="confirmed" value="1" required>
                                        <label class="form-check-label fw-bold" for="confirmed">
                                            I understand the risks and confirm that I want to execute this operation
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <!-- Parameter Input Fields -->
                            @php
                                $parameters = $operationDetails['parameters'] ?? [];
                                $isDbSeed = request()->route('operation') === 'db:seed';
                            @endphp
                            @if($isDbSeed)
                                <div class="alert alert-secondary">
                                    <h6 class="alert-heading"><strong>🌱 Select Seeder</strong></h6>
                                    <p class="mb-3">Choose a seeder to run, or run the default DatabaseSeeder:</p>

                                    <div id="seederLoading" class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="ms-2">Loading available seeders...</span>
                                    </div>

                                    <div id="seederError" class="alert alert-danger d-none"></div>

                                    <div id="seederList" class="d-none">
                                        <div class="form-check mb-2 p-2 bg-light rounded">
                                            <input class="form-check-input" type="radio" name="parameters[class]" value="" id="seeder_all" checked>
                                            <label class="form-check-label fw-bold" for="seeder_all">
                                                🔄 Run All (DatabaseSeeder - default)
                                            </label>
                                            <small class="form-text d-block">Runs the main DatabaseSeeder which typically calls all other seeders</small>
                                        </div>

                                        <hr>
                                        <p class="small text-muted mb-2"><strong>Or select a specific seeder:</strong></p>

                                        <div id="seederItems">
                                            <!-- Seeder items will be injected here via JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            @elseif(count($parameters) > 0)
                                <div class="alert alert-secondary">
                                    <h6 class="alert-heading"><strong>📝 Required Parameters</strong></h6>
                                    <p class="mb-3">This operation requires the following parameters:</p>
                                    
                                    @foreach($parameters as $param)
                                        <div class="mb-3">
                                            <label for="param_{{ $param['name'] }}" class="form-label fw-bold">
                                                {{ ucfirst(str_replace('_', ' ', $param['name'])) }}
                                                @if($param['required'] ?? false)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <input 
                                                type="{{ $param['type'] ?? 'text' }}" 
                                                class="form-control" 
                                                id="param_{{ $param['name'] }}" 
                                                name="parameters[{{ $param['name'] }}]"
                                                placeholder="{{ $param['description'] ?? '' }}"
                                                {{ ($param['required'] ?? false) ? 'required' : '' }}
                                            >
                                            @if(isset($param['description']))
                                                <small class="form-text text-muted">{{ $param['description'] }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="executeBtn">
                                Execute Operation
                            </button>
                            <a href="{{ route('applications.operations.index', $application) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($operationDetails)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Operation Details</h6>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt class="small text-muted">Category</dt>
                            <dd>{{ ucfirst($operationDetails['category'] ?? 'other') }}</dd>

                            <dt class="small text-muted">Risk Level</dt>
                            <dd>
                                @if(($operationDetails['risk_level'] ?? 'medium') === 'none')
                                    <span class="badge bg-success">Safe</span>
                                @elseif(($operationDetails['risk_level'] ?? 'medium') === 'low')
                                    <span class="badge bg-info">Low Risk</span>
                                @elseif(($operationDetails['risk_level'] ?? 'medium') === 'medium')
                                    <span class="badge bg-warning">Medium Risk</span>
                                @else
                                    <span class="badge bg-danger">High Risk</span>
                                @endif
                            </dd>

                            <dt class="small text-muted">Requires Confirmation</dt>
                            <dd>{{ ($operationDetails['requires_confirmation'] ?? false) ? 'Yes' : 'No' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const executeBtn = document.getElementById('executeBtn');

    if (form && executeBtn) {
        form.addEventListener('submit', function(e) {
            const riskLevel = @json($operationDetails['risk_level'] ?? 'medium');
            const operationName = @json($operationDetails['name'] ?? '');

            // Show confirmation dialog for medium and high risk operations
            if (riskLevel === 'high' || riskLevel === 'medium') {
                const message = riskLevel === 'high'
                    ? `⚠️ HIGH RISK OPERATION ️\n\nAre you absolutely sure you want to execute "${operationName}"?\n\nThis operation can cause significant changes to your application.`
                    : `⚠️ Operation Confirmation\n\nAre you sure you want to execute "${operationName}"?`;

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Load seeders if this is the db:seed operation
    const isDbSeed = @json($isDbSeed ?? false);
    if (isDbSeed) {
        loadSeeders();
    }
});

function loadSeeders() {
    const loadingEl = document.getElementById('seederLoading');
    const errorEl = document.getElementById('seederError');
    const listEl = document.getElementById('seederList');
    const itemsEl = document.getElementById('seederItems');

    const seedersUrl = @json(route('applications.operations.seeders', $application));

    fetch(seedersUrl)
        .then(response => response.json())
        .then(data => {
            loadingEl.classList.add('d-none');

            if (data.success && data.seeders && data.seeders.length > 0) {
                listEl.classList.remove('d-none');

                data.seeders.forEach(function(seeder) {
                    const div = document.createElement('div');
                    div.className = 'form-check mb-1 p-2';

                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'radio';
                    input.name = 'parameters[class]';
                    input.value = seeder.fqcn;
                    input.id = 'seeder_' + seeder.class_name;

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = 'seeder_' + seeder.class_name;
                    label.innerHTML = '<strong>' + seeder.class_name + '</strong>';

                    const small = document.createElement('small');
                    small.className = 'form-text d-block text-muted';
                    small.textContent = seeder.file;

                    div.appendChild(input);
                    div.appendChild(label);
                    div.appendChild(small);
                    itemsEl.appendChild(div);
                });
            } else if (data.success) {
                listEl.classList.remove('d-none');
                const noSeeders = document.createElement('p');
                noSeeders.className = 'text-muted mb-0';
                noSeeders.textContent = 'No specific seeder files found. Only the default DatabaseSeeder is available.';
                itemsEl.appendChild(noSeeders);
            } else {
                errorEl.textContent = data.error || 'Failed to load seeders';
                errorEl.classList.remove('d-none');
            }
        })
        .catch(function(err) {
            loadingEl.classList.add('d-none');
            errorEl.textContent = 'Connection error: ' + err.message;
            errorEl.classList.remove('d-none');
        });
}
</script>
@endpush
@endsection
