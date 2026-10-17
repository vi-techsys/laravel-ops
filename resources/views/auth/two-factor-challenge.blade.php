@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Two-Factor Authentication</h4>
                </div>
                <div class="card-body p-4">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="mb-3">
                        A verification code has been sent to your email address. 
                        Please enter the 6-digit code below.
                    </p>

                    <form method="POST" action="{{ route('two-factor.challenge.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="code" class="form-label">Verification Code</label>
                            <input id="code" type="text" 
                                   class="form-control @error('code') is-invalid @enderror"
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   required 
                                   autofocus
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   placeholder="000000"
                                   style="font-family: monospace; letter-spacing: 0.5em; text-align: center; font-size: 1.5em;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Verify Code
                            </button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <form method="POST" action="{{ route('two-factor.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-link">
                                Resend Code
                            </button>
                        </form>
                    </div>

                    <div class="mt-2 text-center">
                        <a href="{{ route('login') }}" class="btn btn-link">
                            Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
