@extends('layouts.app')
@section('title', 'All lecturers')
@section('body-class', 'page-lecturers')

@section('content')
<div class="dir-wrapper">

    <div class="dir-header">
        <h1>👨‍🏫 FCSIT Lecturer Directory</h1>
        <p>{{ $count }} lecturers in the recommendation database</p>
    </div>

    <div class="dir-grid">
        @forelse($lecturers as $lec)
        @php
            $tierClass = match($lec['quality_tier']) {
                'RICH'        => 'tier-rich',
                'MODERATE'    => 'tier-moderate',
                'SPARSE', 'VERY_SPARSE' => 'tier-sparse',
                default       => 'tier-empty',
            };
            $name     = preg_replace('/[^a-zA-Z ]/', '', $lec['name']);
            $initials = strtoupper(substr($name, 0, 1))
                      . strtoupper(ltrim(substr(strrchr($name, ' '), 0, 2)));
        @endphp
        <div class="dir-card">
            <div class="dir-photo">
                @if($lec['img_url'])
                    <img src="{{ $lec['img_url'] }}" alt="{{ $lec['name'] }}"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="dir-initials" style="display:none">{{ $initials }}</div>
                @else
                    <div class="dir-initials">{{ $initials }}</div>
                @endif
            </div>
            <div>
                <div class="quality-badge {{ $tierClass }}">{{ $lec['quality_tier'] }}</div>
                <p class="dir-name">{{ $lec['name'] }}</p>
                @if($lec['summary'])
                    <p class="dir-summary">{{ Str::limit($lec['summary'], 100) }}</p>
                @endif
                <div class="dir-counts">
                    <span>📄 {{ $lec['num_articles'] }}</span>
                    <span>📰 {{ $lec['num_proceedings'] }}</span>
                </div>
                @if($lec['google_scholar'])
                    <a href="{{ $lec['google_scholar'] }}" target="_blank" class="dir-link">Scholar →</a>
                @endif
            </div>
        </div>
        @empty
            <p style="color:#777;font-style:italic">No lecturers found. Make sure the API is running.</p>
        @endforelse
    </div>

</div>
@endsection