<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - FYP Supervisor Recommender</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="@yield('body-class', 'page-auth')">

{{-- Minimal navbar --}}
<nav class="navbar">
    <a href="{{ route('landing') }}" class="nav-logo">
        <div class="logo-box">
            {{-- Logo --}}
             <img src="{{ asset('images/svfinder_logo.svg') }}" alt="Logo">
        </div>
    </a>
    
    <div class="nav-right">
        <a href="{{ route('login') }}"
            class="nav-auth-btn 
            {{ request()->routeIs('login') ? 'nav-auth-btn--filled nav-auth-btn--active' : 'nav-auth-btn--outline' }}">
            Log In
        </a>
        <a href="{{ route('signup') }}"
           class="nav-auth-btn 
           {{ request()->routeIs('signup') ? 'nav-auth-btn--filled nav-auth-btn--active' : 'nav-auth-btn--outline' }}">
            Sign Up
        </a>
    </div>
</nav>

@yield('content')

</body>
</html>