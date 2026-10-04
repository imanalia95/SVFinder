<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SVFinder: FYP Supervisor Recommender')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="@yield('body-class', '')">

{{-- Navbar --}}
<nav class="navbar">
    {{-- Logo --}}
    <a href="{{ session('student_id') ? route('home') : route('landing') }}" class="nav-logo">
        <div class="logo-box">
             <img src="{{ asset('images/svfinder_logo.svg') }}" alt="Logo">
        </div>
    </a>
    
    {{-- Sign up/Log in --}}
    <div class="nav-right">
        @if(session('student_id'))
            {{-- Logged in states --}}

            {{-- Student chip --}}
            <div class="nav-student">
                <div class="nav-student-avatar">
                    {{ strtoupper(substr(session('student_name', 'S'), 0, 1)) }}
                </div>
                <div class="nav-student-info">
                    <span class="nav-student-name">{{ session('student_name') }}</span>
                    <span class="nav-student-matric">{{ session('student_matric') }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="logout-btn" title="Log out">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </button>
                </form>
            </div>

        @else
            {{-- Guest state --}}
            <a href="{{ route('login') }}"
               class="nav-auth-btn nav-auth-btn--outline {{ request()->routeIs('login') ? 'nav-auth-btn--active' : '' }}">
                Log In
            </a>
            <a href="{{ route('signup') }}"
               class="nav-auth-btn nav-auth-btn--filled {{ request()->routeIs('signup') ? 'nav-auth-btn--active' : '' }}">
                Sign Up
            </a>
        @endif

    </div>
</nav>

{{-- Flash banners --}}
@if(session('success'))
    <div class="flash-banner flash-banner--success" id="flashBanner">
        {{ session('success') }}
        <button onclick="this.parentElement.remove()" class="flash-close">×</button>
    </div>
@endif
@if ($errors->has('api'))
    <div class="error-banner">⚠ {{ $errors->first('api') }}</div>
@endif

@yield('content')

<script>
const fb = document.getElementById('flashBanner');
if (fb) setTimeout(() => fb && fb.remove(), 4000);
</script>

</body>
</html>
