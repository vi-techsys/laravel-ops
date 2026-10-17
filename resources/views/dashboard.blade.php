@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Centralized Laravel Management</h1>
                    <p class="lead mb-4">
                        Monitor, diagnose, and manage multiple Laravel applications from one central dashboard.
                        Execute approved operations, track health status, and maintain control across your infrastructure.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('health') }}" class="btn btn-light btn-lg">
                            View System Health
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
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            📊
                        </div>
                        <h6 class="text-muted text-uppercase small mb-2">Applications</h6>
                        <h2 class="fw-bold mb-0">0</h2>
                        <small class="text-muted">Registered targets</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            ✓
                        </div>
                        <h6 class="text-muted text-uppercase small mb-2">Reachable</h6>
                        <h2 class="fw-bold mb-0">0</h2>
                        <small class="text-muted">Healthy connections</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            ⚠
                        </div>
                        <h6 class="text-muted text-uppercase small mb-2">Attention</h6>
                        <h2 class="fw-bold mb-0">0</h2>
                        <small class="text-muted">Need monitoring</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Getting Started Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h3 class="fw-bold mb-4">Getting Started</h3>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            1
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-2">Register Applications</h5>
                                        <p class="text-muted mb-0">
                                            Add your Laravel applications to the management dashboard with their connection details.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            2
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-2">Deploy Connectors</h5>
                                        <p class="text-muted mb-0">
                                            Install the lightweight connector on each managed application for secure communication.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            3
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-2">Monitor Health</h5>
                                        <p class="text-muted mb-0">
                                            Track connection status, view health history, and receive alerts for issues.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            4
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-2">Execute Operations</h5>
                                        <p class="text-muted mb-0">
                                            Run approved Artisan commands, clear caches, manage migrations, and more.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <strong>Stage 1 Complete!</strong>
                                </div>
                                <div>
                                    The foundation is ready. Next: Authentication & Application Registry (Stage 2).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <strong>{{ config('app.name', 'Laravel Ops') }}</strong> — Centralized Multi-Application Management Platform
            </p>
            <small class="text-muted">
                Running on Laravel {{ app()->version() }} | PHP {{ PHP_VERSION }}
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
