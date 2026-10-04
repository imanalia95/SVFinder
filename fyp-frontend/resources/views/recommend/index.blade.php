@extends('layouts.app')
@section('title', 'SVFinder')
@section('body-class', 'page-home')

@section('content')

{{-- PDF.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

{{-- Mammoth.js for .doc/.docx --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>

<div class="home-wrapper">

    {{-- Truncation flash banner --}}
    <div id="truncateBanner" class="truncate-banner" style="display:none;" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>Document exceeds the word limit — please shorten your input to <strong>{{ $maxWords }} words</strong>.</span>
        <button class="truncate-banner-close" onclick="closeBanner()" aria-label="Dismiss">&times;</button>
    </div>

    <div class="home-inner">
    
        {{-- Heading --}}
        <div class="home-heading">
            <h1>Make Your Supervisor Search Faster</h1>
            <p>Input or Upload Your FYP Title or Description</p>
        </div>

        {{-- Input card --}}
        <form action="{{ route('recommend') }}" method="POST" enctype="multipart/form-data" id="searchForm">
            @csrf
            {{-- Hidden fields --}}
            <input type="hidden" name="top_n" value="3">
            <div class="input-card">

                {{--Combined title + description textarea --}}
                <textarea
                    name="title"
                    id="mainInput"
                    class="main-textarea"
                    placeholder="e.g: IoT-Based Smart Energy Monitoring System"
                    required
                >{{ old('title') }}</textarea>

                {{-- Word counter --}}
                <div class="word-counter" id="wordCounter">
                    <span id="wordCount">0</span> / {{ $maxWords }} words
                </div>

                {{-- Desc carried via JS to hidden field --}}
                <input type="hidden" name="description" id="descriptionHidden" value="{{ old('description') }}">

                {{-- Card footer --}}
                <div class="input-footer">
                    <div class="input-footer-left">
                        <label for="docUpload" class="upload-btn">
                            Upload Document
                            <input 
                                type="file" 
                                id="docUpload" 
                                accept=".pdf,.doc,.docx,.txt" 
                                style="display:none" 
                                onchange="readFile(this)"
                            >
                        </label>
                        <span id="uploadName" class="upload-name"></span>
                    </div>
                    <button type="submit" class="send-btn" id="sendBtn" title="Find supervisors">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
const MAX_WORDS = {{ $maxWords }};

// Helpers
function countWords(text) {
    return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
}

function updateCounter(wordCount) {
    const counter = document.getElementById('wordCounter');
    const countEl = document.getElementById('wordCount');
    const sendBtn = document.getElementById('sendBtn');

    countEl.textContent = wordCount;

    if (wordCount > MAX_WORDS) {
        counter.classList.add('over-limit');
        sendBtn.disabled = true;
        sendBtn.title = 'Please shorten your input to under ' + MAX_WORDS + ' words';
    } else {
        counter.classList.remove('over-limit');
        sendBtn.disabled = false;
        sendBtn.title = 'Find supervisors';
    }
}

// Flash Banner
function showBanner() {
    const banner = document.getElementById('truncateBanner');
    banner.style.display = 'flex';
    banner.classList.remove('banner-hide');
    setTimeout(closeBanner, 6000);
}

function closeBanner() {
    const banner = document.getElementById('truncateBanner');
    banner.classList.add('banner-hide');
    setTimeout(() => {
        banner.style.display = 'none';
        banner.classList.remove('banner-hide');
    }, 300);
}

// Apply extracted text
function applyText(text, filename) {
    const uploadName = document.getElementById('uploadName');
    const wordCount  = countWords(text);

    if (wordCount > MAX_WORDS) {
        // Over limit 
        uploadName.textContent = filename + ' (exceeds word limit)';
        uploadName.style.color = '#e07b00';
        document.getElementById('mainInput').value = '';
        showBanner();
        updateCounter(0);
    } else {
        uploadName.textContent = filename;
        uploadName.style.color = '';
        document.getElementById('mainInput').value = text;
        updateCounter(wordCount);
    }
}

// File reader
function readFile(input) {
    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();

    // Plain text
    if (ext === 'txt') {
        const reader = new FileReader();
        reader.onload = e => applyText(e.target.result, file.name);
        reader.readAsText(file);

    // PDF
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

                Promise.all(pagePromises).then(pages => {
                    applyText(pages.join('\n'), file.name);
                });
            });
        });

    // Word (.doc / .docx)
    } else if (ext === 'doc' || ext === 'docx') {
        file.arrayBuffer().then(buffer => {
            mammoth.extractRawText({ arrayBuffer: buffer })
                   .then(result => applyText(result.value, file.name));
        });
    }
}

// Live counter as user types
document.getElementById('mainInput').addEventListener('input', function () {
    updateCounter(countWords(this.value));
});

// Initialise counter on page load (handles old() repopulation)
updateCounter(countWords(document.getElementById('mainInput').value));

// Submit loading state
document.getElementById('searchForm').addEventListener('submit', function () {
    const btn = document.getElementById('sendBtn');
    btn.classList.add('sending');
    btn.innerHTML = '<div class="spinner"></div>';
});
</script>

@endsection