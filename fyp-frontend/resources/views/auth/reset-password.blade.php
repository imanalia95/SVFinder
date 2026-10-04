@extends('layouts.auth')
@section('title', 'Reset Password')
@section('body-class', 'page-auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Choose a new password for your account</p>

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('password.reset.update') }}" method="POST" class="auth-form">
            @csrf

            {{-- Hidden token passed from verification step --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- New password --}}
            <div class="field">
                <label for="password" class="field-label">
                    New Password
                    <span class="field-hint">min 8 characters, include letters and numbers</span>
                </label>
                <div class="field-password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="field-input @error('password') field-input--error @enderror"
                        placeholder="Enter new password"
                        autofocus
                        required
                        oninput="checkStrength(this.value)"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" tabindex="-1">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                {{-- Strength bar --}}
                <div class="strength-bar" id="strengthBar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <p class="strength-label" id="strengthLabel"></p>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm new password --}}
            <div class="field">
                <label for="password_confirmation" class="field-label">Confirm New Password</label>
                <div class="field-password-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="field-input"
                        placeholder="Re-enter new password"
                        required
                        oninput="checkMatch()"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('password_confirmation', this)" tabindex="-1">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p class="field-match" id="matchMsg"></p>
            </div>

            <button type="submit" class="auth-btn">Reset Password</button>
        </form>
    </div>
</div>

<script>
// Password strength
function checkStrength(val) {
    let score = 0;
    if (val.length >= 8)          score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const fill    = document.getElementById('strengthFill');
    const label   = document.getElementById('strengthLabel');
    const colours = ['#e5e5e5', '#ef4444', '#f59e0b', '#3b82f6', '#0d7a4e'];
    const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    fill.style.width      = (score * 25) + '%';
    fill.style.background = colours[score];
    label.textContent     = labels[score];
    label.style.color     = colours[score];
}

// Password match indicator
function checkMatch() {
    const pw   = document.getElementById('password').value;
    const conf = document.getElementById('password_confirmation').value;
    const msg  = document.getElementById('matchMsg');
    if (!conf) { msg.textContent = ''; return; }
    if (pw === conf) {
        msg.textContent = '✓ Passwords match';
        msg.style.color = '#0d7a4e';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.style.color = '#ef4444';
    }
}

// Show/hide password
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.style.opacity = isHidden ? '1' : '0.45';
}
</script>

@endsection

