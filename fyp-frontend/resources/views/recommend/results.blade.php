@extends('layouts.app')
@section('title', 'SVFinder - Recommended Supervisors')
@section('body-class', 'page-results')

@section('content')

{{-- PDF.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

{{-- Mammoth.js for .doc/.docx --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>

{{-- Main Dashboard --}}
<div class="dashboard-wrapper" id="dashboardWrapper">

    <h2 class="dash-title">Recommended Supervisors For You</h2>

    @if(session('error'))
    <div class="flash-banner flash-banner--error" id="flashBanner">
        <span>{{ session('error') }}</span>
        <button class="flash-close" onclick="closeFlash()">×</button>
    </div>
    @endif

    {{-- Split container --}}
    <div class="split-container" id="splitContainer">

    {{-- Lecturer result cards --}}
    <div class="results-list" id="resultsList">
        @forelse($top_hits as $i => $hit)
        @php
            $rankNums = ['1st', '2nd', '3rd'];
            $rankNum = $rankNums[$i] ?? '#'.($i+1);
            $simPct = round($hit['similarity'] * 100, 1);
            $moved = $hit['llm_rank'] - $hit['vector_rank'];
            $tagClass = 'tag-relevant';
            $tagText  = 'Relevancy';
            $nameClean = preg_replace('/[^a-zA-Z ]/', '', $hit['name']);
            $initials  = strtoupper(substr($nameClean, 0, 1)).strtoupper(ltrim(substr(strrchr($nameClean,' '),0,2)));
            $emailAddr  = trim($hit['email'] ?? '');
            $hasEmail   = $emailAddr !== '';
        @endphp

        <div class="result-row {{ $i === 0 ? 'row-selected' : '' }}" data-index="{{ $i }}"
             onclick="highlightCard({{ $i }})">
            
            {{-- Photo thumbnail --}}
            <div class="result-thumb">
                @if($hit['img_url'])
                    <img src="{{ $hit['img_url'] }}" alt=" {{ $hit['name'] }}"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="thumb-initials" style="display:none">{{ $initials }}</div>
                @else
                    <div class="thumb-initials">{{ $initials }}</div>
                @endif
                <span class="thumb-rank">{{ $rankNum }}</span>
            </div>

            {{-- Info --}}
             <div class="result-info">
                <div class="result-info-top">
                    <h3 class="result-name">{{ $hit['name'] }}</h3>
                    <div class="result-tags">
                        <span class="match-tag {{ $tagClass }}">{{ $tagText }}</span>
                        <span class="sim-pct">{{ $simPct }}%</span>
                    </div>
                </div>

                {{-- Similarity bar --}}
                 <div class="sim-track">
                    <div class="sim-fill" style="width:{{ $simPct }}%"></div>
                </div>

                {{-- Summary preview --}}
                @if($hit['summary'])
                <p class="result-preview">{{ Str::limit($hit['summary'], 300) }}</p>
                @endif

                {{-- stats --}}
                <div class="result-stats">
                    <span>📄 {{ $hit['num_articles'] }} articles</span>
                    <span>📰 {{ $hit['num_proceedings'] }} proceedings</span>
                </div>

                {{-- Action buttons --}}
                <div class="card-actions">
                    @if($hasEmail)
                        <a href="mailto:{{ $emailAddr }}?subject=FYP Supervision Enquiry&body=Dear {{ rawurlencode($hit['name']) }},%0A%0AI am a final year Computer Science student at FCSIT UNIMAS and I am interested in discussing FYP supervision opportunities with you. My proposed FYP is:%0A%0A{{ rawurlencode('"'.$title.'"') }}%0A%0AThank you.%0A%0ARegards,"
                        class="card-btn card-btn--email"
                        title="Email {{ $hit['name'] }}"
                        onclick="event.stopPropagation()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Email
                        </a>
                    @else
                        <span class="card-btn card-btn--email card-btn--na" title="Email not available">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Email →
                        </span>
                    @endif

                    @if(!empty($hit['google_scholar']))
                        <a href="{{ $hit['google_scholar'] }}" target="_blank"
                        class="card-btn card-btn--scholar"
                        onclick="event.stopPropagation()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            Scholar →
                        </a>
                    @endif
                </div>
            </div>

        </div>
        @empty
            <div class="empty-msg">No results. Try a more descriptive input.</div>
        @endforelse

        {{-- Open Analysis Recommendation Button --}}
        @if($reasoning)
        <div class="global-analysis-row">
            <button type="button" class="global-analysis-btn" id="openAnalysisBtn"
                    onclick="openAnalysisPanel()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Open Recommendation Analysis →
            </button>
            <p class="global-analysis-hint">
                Why these {{ count($top_hits) }} lecturers were selected for your FYP topic
            </p>
        </div>
        @endif
    </div>

    {{-- Inline Analysis Panel (hidden until opened) --}}
    @if($reasoning)
    <div class="inline-analysis" id="inlineAnalysis">

        {{-- Header --}}
        <div class="panel-header">
            <div class="panel-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <h3 class="panel-title">Recommendation Analysis</h3>
            </div>
            <button class="panel-close" onclick="closeAnalysisPanel()" title="Close analysis">×</button>
        </div>

        <p class="panel-subtitle">Why these lecturers were selected for your FYP topic</p>

        {{-- Analysis thread --}}
        <div class="analysis-thread" id="analysisThread">

            <div class="bubble-row bubble-appear" style="--delay: 0s">
                <div class="bubble-avatar">🤖</div>
                <div class="bubble bubble--intro">Here is my analysis of the top {{ count($top_hits) }} recommended supervisors for your FYP topic.
                </div>
            </div>

            @php
                $blocks = preg_split('/(?=\*\*(?:#|\[|[0-9]|Overall))/', trim($reasoning));
                $blocks = array_values(array_filter($blocks, fn($b) => strlen(trim($b)) > 10));
                if (count($blocks) <= 1) { $blocks = [trim($reasoning)]; }
            @endphp

            @foreach($blocks as $bi => $block)
            <div class="bubble-row bubble-appear" style="--delay: {{ ($bi + 1) * 0.18 }}s"
                 data-block="{{ $bi }}">
                <div class="bubble-avatar">🤖</div>
                <div class="bubble bubble--reasoning {{ $bi === 0 ? 'bubble--active' : '' }}">{!! nl2br(e(trim($block))) !!}
                </div>
            </div>
            @endforeach

        </div>

        <div class="panel-footer">
            <span>Generated by {{ $meta['llm_model'] ?? 'ChatGLM3' }}</span>
            @if(isset($meta['elapsed_seconds']))
                <span>· {{ $meta['elapsed_seconds'] }}s</span>
            @endif
        </div>
        
    </div>{{-- /inline-analysis --}}
    @endif

    </div>{{-- /split-container --}}

    {{-- Persistent Bottom input bar --}}
    <div class="bottom-bar">

        {{-- Word counter row (shown only when user starts typing) --}}
        <div class="bottom-counter-row" id="bottomCounterRow">
            <span id="bottomWordCount">0</span> / {{ $maxWords }} words
        </div>

        {{-- Over-limit warning --}}
        <div class="bottom-over-limit" id="bottomOverLimit" style="display:none;">
            Input exceeds {{ $maxWords }} words — please shorten before searching.
        </div>

        <form action="{{ route('recommend') }}" method="POST" class="bottom-form" id="bottomForm">
            @csrf
            <input type="hidden" name="top_n" value="3">

            {{-- Upload button --}}
            <label for="bottomUpload" class="bottom-upload-btn" id="bottomUploadLabel" title="Upload document">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <input type="file" id="bottomUpload" style="display:none" accept=".pdf,.doc,.docx,.txt"
                       onchange="bottomReadFile(this)">
            </label>

            <input
                type="text"
                name="title"
                id="bottomInput"
                class="bottom-input"
                placeholder="Refine your title or enter a new title..."
                required
                autocomplete="off"
            >
            <button type="submit" class="bottom-send" id="bottomSendBtn" title="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>

        {{-- Upload file name indicator --}}
        <div class="bottom-upload-name" id="bottomUploadName" style="display:none;"></div>

    </div>

</div>{{-- /dashboard-wrapper --}}

<script>
const BOTTOM_MAX_WORDS = {{ $maxWords }};

// Helpers
function countWords(text) {
    return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
}

function updateBottomCounter(wordCount) {
    const counterRow = document.getElementById('bottomCounterRow');
    const countEl    = document.getElementById('bottomWordCount');
    const overLimit  = document.getElementById('bottomOverLimit');
    const sendBtn    = document.getElementById('bottomSendBtn');

    countEl.textContent = wordCount;

    // Show counter row once user starts typing
    counterRow.style.display = wordCount > 0 ? 'block' : 'none';

    if (wordCount > BOTTOM_MAX_WORDS) {
        counterRow.classList.add('over-limit');
        overLimit.style.display = 'block';
        sendBtn.disabled = true;
        sendBtn.title = 'Shorten your input to under ' + BOTTOM_MAX_WORDS + ' words';
    } else {
        counterRow.classList.remove('over-limit');
        overLimit.style.display = 'none';
        sendBtn.disabled = false;
        sendBtn.title = 'Search';
    }
}

// Live counter on bottom input 
document.getElementById('bottomInput').addEventListener('input', function () {
    updateBottomCounter(countWords(this.value));
});

// File upload for bottom bar
function bottomReadFile(input) {
    const file = input.files[0];
    if (!file) return;

    const ext        = file.name.split('.').pop().toLowerCase();
    const nameEl     = document.getElementById('bottomUploadName');
    const bottomInpt = document.getElementById('bottomInput');

    function applyBottomText(text) {
        const wordCount = countWords(text);

        if (wordCount > BOTTOM_MAX_WORDS) {
            // Over limit — show warning, do NOT fill input
            nameEl.textContent  = file.name + ' (exceeds word limit)';
            nameEl.style.color  = '#e07b00';
            nameEl.style.display = 'block';
            bottomInpt.value    = '';
            updateBottomCounter(0);
        } else {
            // Within limit — fill the input
            nameEl.textContent  = file.name;
            nameEl.style.color  = '';
            nameEl.style.display = 'block';
            bottomInpt.value    = text;
            updateBottomCounter(wordCount);
        }
    }

    if (ext === 'txt') {
        const reader = new FileReader();
        reader.onload = e => applyBottomText(e.target.result);
        reader.readAsText(file);

    } else if (ext === 'pdf') {
        file.arrayBuffer().then(buffer => {
            pdfjsLib.getDocument({ data: buffer }).promise.then(pdf => {
                const pagePromises = [];
                for (let i = 1; i <= pdf.numPages; i++) {
                    pagePromises.push(
                        pdf.getPage(i).then(page =>
                            page.getTextContent().then(content =>
                                content.items.map(item => item.str).join(' ')
                            )
                        )
                    );
                }
                Promise.all(pagePromises).then(pages => applyBottomText(pages.join('\n')));
            });
        });

    } else if (ext === 'doc' || ext === 'docx') {
        file.arrayBuffer().then(buffer => {
            mammoth.extractRawText({ arrayBuffer: buffer })
                   .then(result => applyBottomText(result.value));
        });
    }
}

// Block form submit if over limit
document.getElementById('bottomForm').addEventListener('submit', function (e) {
    const wordCount = countWords(document.getElementById('bottomInput').value);
    if (wordCount > BOTTOM_MAX_WORDS) {
        e.preventDefault();
        updateBottomCounter(wordCount); // re-trigger warning UI
        return;
    }
    // Loading state
    const btn = document.getElementById('bottomSendBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div>';
});

// Flash banner auto close
function closeFlash() {
    const banner = document.getElementById('flashBanner');
    if (banner) banner.remove();
}

document.addEventListener("DOMContentLoaded", function () {
    const banner = document.getElementById('flashBanner');
    if (banner) {
        setTimeout(() => banner.remove(), 2500);
    }
});

// Analysis panel - inline split
function openAnalysisPanel() {
    const container = document.getElementById('splitContainer');
    const panel     = document.getElementById('inlineAnalysis');
    const thread    = document.getElementById('analysisThread');

    if (!container || !panel) return;

    container.classList.add('analysis-open');

    // Reset scroll to top
    if (thread) thread.scrollTop = 0;

    // Re-trigger staggered animations
    document.querySelectorAll('.bubble-appear').forEach(el => {
        el.style.animation = 'none';
        void el.offsetWidth;
        el.style.animation = '';
    });

    // After the flex transition, scroll to the active bubble
    setTimeout(() => {
        document.querySelectorAll('.bubble--reasoning').forEach(b => b.classList.remove('bubble--active'));
        const bubble = document.querySelector(
            `.bubble-row[data-block="${selectedIndex}"] .bubble--reasoning`
        );
        if (bubble) {
            bubble.classList.add('bubble--active');
            bubble.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 450);
}

function closeAnalysisPanel() {
    const container = document.getElementById('splitContainer');
    if (container) container.classList.remove('analysis-open');
}

function closeAllPanels() {
    closeAnalysisPanel(); 
}

// Card highlight
let selectedIndex = 0; 

function highlightCard(index) {
    selectedIndex = index;

    document.querySelectorAll('.result-row').forEach(r => r.classList.remove('row-selected'));
    document.querySelector(`.result-row[data-index="${index}"]`).classList.add('row-selected');

    document.querySelectorAll('.bubble--reasoning').forEach(b => b.classList.remove('bubble--active'));

    const bubble = document.querySelector(`.bubble-row[data-block="${index}"] .bubble--reasoning`);
    if (bubble) {
        bubble.classList.add('bubble--active');
        const container = document.getElementById('splitContainer');
        if (container && container.classList.contains('analysis-open')) {
            bubble.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}
</script>

@endsection


                