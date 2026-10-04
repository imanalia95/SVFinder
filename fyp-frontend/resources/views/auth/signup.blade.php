@extends('layouts.auth')
@section('title', 'Sign Up')
@section('body-class', 'page-auth')

@section('content')
<div class="auth-wrapper">

    <div class="auth-card auth-card--wide">

        <h1 class="auth-title">Create your account</h1>
        <p class="auth-subtitle">UNIMAS students only · all fields required</p>

        <form action="{{ route('signup') }}" method="POST" class="auth-form" id="signupForm">
            @csrf

            {{-- Name --}}
            <div class="field">
                <label for="name" class="field-label">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="field-input @error('name') field-input--error @enderror"
                    placeholder="e.g Ali bin Abu"
                    value="{{ old('name') }}"
                    autofocus
                    required
                >
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Matric number --}}
            <div class="field">
                <label for="matric_number" class="field-label">
                    Matric Number
                    <span class="field-hint">5 or 6 digits · Your student ID</span>
                </label>
                <input
                    type="text"
                    id="matric_number"
                    name="matric_number"
                    class="field-input @error('matric_number') field-input--error @enderror"
                    placeholder="e.g. 12345 or 123456"
                    value="{{ old('matric_number') }}"
                    inputmode="numeric"
                    maxlength="6"
                    pattern="\d{5,6}"
                    title="5 or 6 digit number"
                    required
                >
                <div class="matric-meter" id="matricMeter">
                    <div class="matric-meter-fill" id="matricFill"></div>
                </div>
                <p class="matric-counter" id="matricCounter">0 / 6 digits</p>
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
                    placeholder="e.g. ahmad@student.unimas.my"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Passwprd --}}
            <div class="field">
                <label for="password" class="field-label">
                    Password 
                    <span class="field-hint">min 8 characters, include letters and numbers</span>
                </label>
                <div class="field-password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="field-input @error('password') field-input--error @enderror"
                        placeholder="Create a password"
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

            {{-- Confirm Password --}}
            <div class="field">
                <label for="password_confirmation" class="field-label">Confirm Password</label>
                <div class="field-password-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="field-input"
                        placeholder="Re-enter your password"
                        required
                        oninput="checkMatch()"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('password_confirmation', this)" tabindex="-1">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p class="field-match" id="matchMsg"></p>
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="{{ route('login') }}" class="auth-link-2">Log in</a>
        </p>

    </div>
</div>

<script>
// Matric counter + meter
const matricInput = document.getElementById('matric_number');
const matricFill  = document.getElementById('matricFill');
const matricCounter = document.getElementById('matricCounter');

matricInput.addEventListener('input', function () {
    // Strip non-digits
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
    const len  = this.value.length;
    matricCounter.textContent = len + ' / 6 digits';
    const pct  = (len / 6) * 100;
    matricFill.style.width = pct + '%';
    matricFill.style.background = len >= 5 ? '#0d7a4e' : (len >= 3 ? '#b45309' : '#ddd');
});

// Password strength
function checkStrength(val) {
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    const colours = ['#e5e5e5', '#ef4444', '#f59e0b', '#3b82f6', '#0d7a4e'];
    const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    fill.style.width      = (score * 25) + '%';
    fill.style.background = colours[score];
    label.textContent     = labels[score];
    label.style.color     = colours[score];
}

// Password match indicator 
function checkMatch() {
    const pw    = document.getElementById('password').value;
    const conf  = document.getElementById('password_confirmation').value;
    const msg   = document.getElementById('matchMsg');
    if (!conf) { msg.textContent = ''; return; }
    if (pw === conf) {
        msg.textContent = '✓ Passwords match';
        msg.style.color = '#0d7a4e';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.style.color = '#ef4444';
    }
}

// Show / hide password 
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const hide  = input.type === 'password';
    input.type  = hide ? 'text' : 'password';
    btn.style.opacity = hide ? '1' : '.45';
}
</script>
@endsection   


