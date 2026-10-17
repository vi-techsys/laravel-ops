@extends('layouts.app')

@section('title', 'Two-Factor Authentication Code')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Your Two-Factor Authentication Code</h4>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3">Hello {{ $user->name }},</p>
                    
                    <p class="mb-3">Your verification code is:</p>
                    
                    <div class="alert alert-primary text-center">
                        <h2 class="mb-0" style="letter-spacing: 0.5em; font-family: monospace;">{{ $code }}</h2>
                    </div>
                    
                    <p class="mb-3">This code will expire in 10 minutes.</p>
                    
                    <p class="mb-0 text-muted small">
                        If you did not request this code, please secure your account immediately.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
