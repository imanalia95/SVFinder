@extends('layouts.landing')
@section('title', 'SVFinder - FYP Supervisor Recommender')

@section('content')

{{-- ── Top-right Log In link with expanding underline on hover ── --}}
<div class="landing-topbar">
    <div class="auth-links">
        {{-- Log In --}}
        <a href="{{ route('login') }}" class="auth-link auth-link--login" id="loginLink">
            Log In
            <span class="auth-underline" id="loginUnderline"></span>
        </a>

        {{-- Sign Up --}}
        <a href="{{ route('signup') }}" class="auth-link auth-link--signup" id="signupLink">
            Sign Up
            <span class="auth-underline" id="signupUnderline"></span>
        </a>
    </div>
</div>

{{-- ── Full-screen hero ── --}}
<main class="landing-hero">


    {{-- Content card —sits on top of the background --}}
    <div class="landing-card">

        {{-- Headline --}}
        <div class="landing-headline">
            <span class="headline-main">
                Make Your <span class="landing-accent">Supervisor</span>
            </span>

            <span class="headline-sub">
                Search Faster
            </span>
        </div>

        {{-- Subtitle --}}
        <p class="landing-subtitle">
            One click away to lessen your Final Year Project stress in
            supervisor hunting. With LLM as your friendly advisor!
        </p>

        {{-- CTA button --}}
        <a href="{{ route('signup') }}" class="landing-cta" id="landingCta">
            Get started &nbsp;→
        </a>

    </div>

</main>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const topbar = document.querySelector('.landing-topbar');
    if (topbar) {
        topbar.style.opacity = '0';
        topbar.style.transform = 'translateY(-10px)';
        topbar.style.transition = 'opacity 0.5s ease 0.4s, transform 0.5s ease 0.4s';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                topbar.style.opacity = '1';
                topbar.style.transform = 'translateY(0)';
            });
        });
    }
});
</script>

@endsection
