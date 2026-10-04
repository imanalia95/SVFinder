@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('body-class', 'page-auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Enter your matric number and registered email to continue</p>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div  class="flash flash-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('password.forgot.verify') }}" method="POST" class="auth-form">
            @csrf

            {{-- Matric number --}}
            <div class="field">
                <label for="matric_number" class="field-label">Matric Number</label>
                <input
                    type="text"
                    id="matric_number"
                    name="matric_number"
                    class="field-input @error('matric_number') field-input--error @enderror"
                    placeholder="e.g. 12345"
                    value="{{ old('matric_number') }}"
                    inputmode="numeric"
                    maxlength="7"
                    autofocus
                    required
                >
                @error('matric_number')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="field">
                <label for="email" class="field-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="field-input @error('email') field-input--error @enderror"
                    placeholder="Enter your registered email"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Verify Identity</button>
        </form>

        <p class="auth-switch">
            Remembered your password?
            <a href="{{ route('login') }}" class="auth-link-2">Log in</a>
        </p>
    </div>
</div>
@endsection