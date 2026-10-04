@extends('layouts.auth')
@section('title', 'Log In')
@section('body-class', 'page-auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Log in with your matric number to continue</p>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash flash-sucess">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="auth-form">
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

            {{-- Password --}}
            <div class="field">
                <div class="field-label-row">
                    <label for="password" class="field-label">Password</label>
                    <a href="{{ route('password.forgot') }}" class="forgot-link">Forgot password?</a>
                </div>
                <div class="field-password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="field-input @error('password') field-input--error @enderror"
                        placeholder="Enter your password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" tabindex="-1">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Log In</button>
        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="{{ route('signup') }}" class="auth-link-2">Sign up</a>
        </p>
    </div>

</div>

<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.style.opacity = isHidden ? '1' : '45';
    }
</script>

@endsection
