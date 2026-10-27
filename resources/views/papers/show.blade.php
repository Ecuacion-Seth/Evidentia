@extends('layouts.app', ['title' => $paper->title . ' - Evidence Integrity Report'])

@section('content')
<div class="flex-1 flex flex-col h-[calc(100vh-4rem)] overflow-hidden">
    <!-- Top Summary Ribbon -->
    <section class="border-b border-slate-800 bg-slate-900/90 px-4 sm:px-6 py-3 flex-shrink-0 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Paper Info -->
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                        {{ $paper->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($paper->status === 'failed' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse') }}">
                        {{ $paper->status }}
                    </span>
                    <span class="text-xs text-slate-400">ID: #{{ $paper->id }} &bull; {{ $paper->created_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-lg font-bold text-white truncate max-w-xl" title="{{ $paper->title }}">{{ $paper->title }}</h1>
            </div>

            <!-- Key Integrity Metrics & Claims Distribution -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Practical Coverage Metric -->
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800" title="Proportion of cited external sources retrievable as open-access full text">
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Practical Coverage</div>
                        <div class="text-sm font-extrabold text-emerald-400 font-mono">{{ number_format($paper->practical_coverage, 1) }}% OA</div>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Claims Breakdown Chips -->
                <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-950 border border-slate-800 text-xs font-semibold">
                    <span class="px-2 py-1 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/20" title="Supported Claims">
                        ✓ {{ $stats['supported'] }} Supported
                    </span>
                    <span class="px-2 py-1 rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/20" title="Partially Supported Claims">
                        ~ {{ $stats['partially_supported'] }} Partial
                    </span>
                    <span class="px-2 py-1 rounded-lg bg-rose-500/15 text-rose-400 border border-rose-500/20" title="Unsupported Claims">
                        ✕ {{ $stats['unsupported'] }} Unsupported
                    </span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 text-slate-400 border border-slate-700" title="Unverifiable (Paywalled or Missing)">
                        ? {{ $stats['unverifiable'] }} Unverifiable
                    </span>
                </div>

                <!-- Supplementary AI Regularity Indicator (With Modal Disclaimer) -->
                @if ($paper->ai_supplementary_score !== null)
                    <div class="relative group cursor-pointer" onclick="document.getElementById('aiDisclaimerModal').classList.remove('hidden')">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/40 transition-colors">
                            <div class="text-right">
                                <div class="text-[10px] uppercase font-bold text-slate-400 flex items-center justify-end gap-1">
                                    AI Regularity
                                    <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-xs font-bold text-indigo-400 font-mono">{{ $paper->ai_supplementary_score }}/100</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions: Export JSON & Re-run -->
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('papers.export.json', $paper) }}" target="_blank"
                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" title="Export JSON Evidence Report">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                    <form action="{{ route('papers.reassess', $paper) }}" method="POST" onsubmit="return confirm('Re-run full assessment pipeline?')">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" title="Re-scan Document">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Split-Screen Interactive Workspace -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 overflow-hidden bg-slate-950">
        <!-- LEFT PANE: Document Viewer with Color-Coded Highlighted Claims (cols 12 -> 6) -->
        <section class="lg:col-span-6 border-r border-slate-800/80 flex flex-col h-full overflow-hidden bg-slate-950">
            <div class="px-6 py-3 border-b border-slate-800/60 bg-slate-900/40 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Document Reader</span>
                </div>
                <span class="text-xs text-slate-400">Click highlighted claims to inspect grounds</span>
            </div>

            <!-- Scrollable Document Text -->
            <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-6 text-slate-200 text-sm sm:text-base leading-relaxed" id="documentBody">
                @if ($paper->document_text)
                    @php
                        // Process the text to wrap matched claims with interactive highlight spans
                        $renderedText = e($paper->document_text);
                        foreach ($paper->claims as $claim) {
                            $escapedClaim = e($claim->claim_text);
                            $badgeClass = match($claim->effective_verdict) {
                                'SUPPORTED' => 'bg-emerald-500/20 text-emerald-300 border-b-2 border-emerald-500 hover:bg-emerald-500/30',
                                'PARTIALLY_SUPPORTED' => 'bg-amber-500/20 text-amber-300 border-b-2 border-amber-500 hover:bg-amber-500/30',
                                'UNSUPPORTED' => 'bg-rose-500/20 text-rose-300 border-b-2 border-rose-500 hover:bg-rose-500/30',
                                default => 'bg-slate-700/40 text-slate-300 border-b-2 border-slate-500 hover:bg-slate-700/60',
                            };

                            $highlightSpan = '<mark class="cursor-pointer rounded px-1.5 py-0.5 transition-all inline ' . $badgeClass . '" data-claim-id="' . $claim->id . '" onclick="focusClaim(' . $claim->id . ')">'
                                . $escapedClaim . ' <span class="text-[10px] font-mono font-bold uppercase px-1 rounded bg-black/40">' . e($claim->in_text_citation) . '</span></mark>';

                            // Replace first exact occurrence
                            $pos = strpos($renderedText, $escapedClaim);
                            if ($pos !== false) {
                                $renderedText = substr_replace($renderedText, $highlightSpan, $pos, strlen($escapedClaim));
                            }
                        }
                    @endphp

                    <div class="prose prose-invert max-w-none text-slate-300 font-sans leading-relaxed whitespace-pre-wrap">
                        {!! $renderedText !!}
                    </div>

                    @if ($paper->references->isNotEmpty())
                        <div class="mt-12 pt-8 border-t border-slate-800">
                            <h2 class="text-base font-bold text-slate-100 uppercase tracking-wider mb-4">References</h2>
                            <div class="space-y-3 font-mono text-xs text-slate-400">
                                @foreach ($paper->references as $ref)
                                    <div class="p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/80">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-bold text-slate-300">{{ $ref->citation_key }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                                {{ $ref->retrieval_status === 'RETRIEVED' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($ref->retrieval_status === 'PAYWALLED' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400') }}">
                                                {{ $ref->retrieval_status }}
                                            </span>
                                        </div>
                                        <p class="leading-relaxed">{{ $ref->raw_text }}</p>
                                        @if ($ref->doi)
                                            <a href="https://doi.org/{{ $ref->doi }}" target="_blank" class="inline-block mt-1 text-indigo-400 hover:underline">
                                                doi:{{ $ref->doi }} &nearr;
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-20 text-slate-400">
                        <p class="text-sm">Document is being analyzed. Please wait a moment...</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- RIGHT PANE: Toulmin Evidence Inspector (cols 12 -> 6) -->
        <section class="lg:col-span-6 flex flex-col h-full overflow-hidden bg-slate-900/30">
            <div class="px-6 py-3 border-b border-slate-800/60 bg-slate-900/60 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Toulmin Evidence Inspector</span>
                </div>
                <span class="text-xs text-slate-400">{{ $paper->claims->count() }} Claims Audited</span>
            </div>

            <!-- Scrollable Cards Feed -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5" id="evidenceInspector">
                @forelse ($paper->claims as $claim)
                    <article id="claim-card-{{ $claim->id }}"
                        class="p-5 rounded-2xl border transition-all duration-300
                            {{ match($claim->effective_verdict) {
                                'SUPPORTED' => 'bg-slate-900/80 border-emerald-500/30 hover:border-emerald-500/60 shadow-lg shadow-emerald-500/5',
                                'PARTIALLY_SUPPORTED' => 'bg-slate-900/80 border-amber-500/30 hover:border-amber-500/60 shadow-lg shadow-amber-500/5',
                                'UNSUPPORTED' => 'bg-slate-900/80 border-rose-500/30 hover:border-rose-500/60 shadow-lg shadow-rose-500/5',
                                default => 'bg-slate-900/80 border-slate-800 hover:border-slate-700',
                            } }}">
                        
                        <!-- Header with Status Badge -->
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="text-xs font-mono font-semibold text-slate-300">
                                    {{ $claim->in_text_citation }}
                                </span>
                            </div>

                            <!-- Verdict Tag -->
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase
                                    {{ match($claim->effective_verdict) {
                                        'SUPPORTED' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                                        'PARTIALLY_SUPPORTED' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                                        'UNSUPPORTED' => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
                                        default => 'bg-slate-800 text-slate-400 border border-slate-700',
                                    } }}">
                                    {{ str_replace('_', ' ', $claim->effective_verdict) }}
                                </span>
                                @if ($claim->instructor_override_verdict)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30" title="Manually overridden by instructor">
                                        Override
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 1. The Claim (Toulmin Claim) -->
                        <div class="mb-4">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">Extracted Student Claim</span>
                            <blockquote class="text-sm font-medium text-slate-100 pl-3 border-l-2 border-indigo-500/50 leading-relaxed">
                                "{{ $claim->claim_text }}"
                            </blockquote>
                        </div>

                        <!-- 2. Grounds / Evidence Quote (Toulmin Grounds) -->
                        <div class="mb-4 p-3.5 rounded-xl bg-slate-950/80 border border-slate-800">
                            <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider mb-1.5">
                                <span class="text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    Grounds: Retrieved Evidence Quote
                                </span>
                                @if ($claim->evidence?->confidence_score)
                                    <span class="text-slate-400 font-mono">{{ round($claim->evidence->confidence_score * 100) }}% match confidence</span>
                                @endif
                            </div>
                            
                            @if ($claim->evidence?->evidence_quote)
                                <p class="text-xs text-slate-200 italic leading-relaxed">
                                    &ldquo;{{ $claim->evidence->evidence_quote }}&rdquo;
                                </p>
                            @else
                                <p class="text-xs text-slate-400 italic">
                                    {{ $claim->evidence?->reasoning_warrant ?? 'No full-text evidence retrieved for this source.' }}
                                </p>
                            @endif
                        </div>

                        <!-- 3. Warrant / Reasoning (Toulmin Warrant) -->
                        @if ($claim->evidence?->reasoning_warrant)
                            <div class="mb-4 text-xs text-slate-300 bg-indigo-950/20 p-3 rounded-lg border border-indigo-500/10">
                                <strong class="text-indigo-400 uppercase text-[10px] tracking-wider block mb-0.5">Warrant & Verification Reasoning:</strong>
                                {{ $claim->evidence->reasoning_warrant }}
                            </div>
                        @endif

                        <!-- 4. Cited Source Bibliographic Information -->
                        @if ($claim->reference)
                            <div class="pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="truncate max-w-sm">
                                    <span class="text-slate-300 font-semibold">{{ $claim->reference->citation_key }}</span>:
                                    <span class="italic text-slate-400">{{ $claim->reference->title }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if ($claim->reference->open_access_url)
                                        <a href="{{ $claim->reference->open_access_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 transition-colors">
                                            Open-Access PDF &nearr;
                                        </a>
                                    @endif
                                    @if ($claim->reference->doi)
                                        <a href="https://doi.org/{{ $claim->reference->doi }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                                            DOI &nearr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- 5. Instructor Review & Manual Override Accordion -->
                        <div class="mt-4 pt-3 border-t border-slate-800/60" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="text-[11px] text-slate-400 hover:text-slate-200 font-medium flex items-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Instructor Review / Override</span>
                            </button>

                            <div x-show="open" class="mt-3 p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                                <form action="{{ route('claims.override', $claim) }}" method="POST">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Override Verdict</label>
                                            <select name="instructor_override_verdict" class="w-full text-xs rounded-lg bg-slate-900 border border-slate-700 text-slate-200 px-2.5 py-1.5 outline-none">
                                                <option value="SUPPORTED" {{ $claim->effective_verdict === 'SUPPORTED' ? 'selected' : '' }}>SUPPORTED</option>
                                                <option value="PARTIALLY_SUPPORTED" {{ $claim->effective_verdict === 'PARTIALLY_SUPPORTED' ? 'selected' : '' }}>PARTIALLY SUPPORTED</option>
                                                <option value="UNSUPPORTED" {{ $claim->effective_verdict === 'UNSUPPORTED' ? 'selected' : '' }}>UNSUPPORTED</option>
                                                <option value="UNVERIFIABLE" {{ $claim->effective_verdict === 'UNVERIFIABLE' ? 'selected' : '' }}>UNVERIFIABLE</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Instructor Review Notes</label>
                                            <input type="text" name="instructor_notes" value="{{ $claim->instructor_notes }}" placeholder="e.g. Verified in printed edition p. 45"
                                                class="w-full text-xs rounded-lg bg-slate-900 border border-slate-700 text-slate-200 px-2.5 py-1.5 outline-none">
                                        </div>
                                    </div>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors">
                                        Save Instructor Decision
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="text-center py-20 text-slate-400">
                        <p class="text-sm">No citations or claims were detected in this document.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<!-- AI Regularity Indicator Disclaimer Modal -->
<div id="aiDisclaimerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
        <div class="flex items-center gap-3 text-amber-400 mb-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-base font-bold text-white">Supplementary AI Regularity Indicator</h3>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed mb-4">
            {{ $paper->ai_disclaimer ?? 'Notice: This score evaluates statistical burstiness and entropy. It does NOT prove AI authorship or ground-truth generation. Academic writing often exhibits low perplexity due to strict formal structures. Evaluate academic integrity exclusively through the inspectable Claim-Evidence audit.' }}
        </p>
        <div class="flex justify-end">
            <button type="button" onclick="document.getElementById('aiDisclaimerModal').classList.add('hidden')"
                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors">
                I Understand
            </button>
        </div>
    </div>
</div>

<script>
    // Synchronous click navigation: clicking highlighted claim highlights & scrolls right card
    function focusClaim(claimId) {
        // Highlight active mark
        document.querySelectorAll('mark[data-claim-id]').forEach(el => el.classList.remove('ring-2', 'ring-indigo-400'));
        const activeMark = document.querySelector('mark[data-claim-id="' + claimId + '"]');
        if (activeMark) {
            activeMark.classList.add('ring-2', 'ring-indigo-400');
        }

        // Scroll evidence card into view
        const card = document.getElementById('claim-card-' + claimId);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('ring-2', 'ring-indigo-500');
            setTimeout(() => card.classList.remove('ring-2', 'ring-indigo-500'), 2000);
        }
    }
</script>
@endsection
