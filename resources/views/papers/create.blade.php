@extends('layouts.app', ['title' => 'Submit Paper for Evidence Verification'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">
    <!-- Hero Header -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EDF2F7] dark:bg-[#15203B] border border-[#E2E8F0] dark:border-[#1E293B] text-[#475569] dark:text-[#94A3B8] text-xs font-medium mb-4">
            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Toulmin-Grounded Academic Evidence Integrity
        </div>
        <h1 class="text-3xl sm:text-4xl font-serif font-bold tracking-tight text-[#0F172A] dark:text-[#F1F5F9] mb-3">
            Verify Whether Sources <span class="italic text-blue-600 dark:text-blue-400 font-serif">Actually Support</span> Claims
        </h1>
        <p class="text-sm sm:text-base text-[#64748B] dark:text-[#94A3B8] max-w-2xl mx-auto leading-relaxed">
            Extract claims and citations from academic research, retrieve open-access peer-reviewed literature via OpenAlex and Crossref, and inspect evidence warrants with precision.
        </p>
    </div>

    <!-- Error Banner -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-800 dark:text-rose-300 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Submission Card -->
    <div class="bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl shadow-xs p-6 sm:p-8">
        <form action="{{ route('papers.store') }}" method="POST" enctype="multipart/form-data" id="scanForm">
            @csrf

            <!-- Paper Title -->
            <div class="mb-6">
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-[#475569] dark:text-[#94A3B8] mb-2">Paper Title / Assignment Name</label>
                <input type="text" name="title" id="title" required
                    class="w-full px-4 py-3 rounded-xl bg-[#F8FAFC] dark:bg-[#0B1120] border border-[#E2E8F0] dark:border-[#1E293B] focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-[#0F172A] dark:text-[#F1F5F9] placeholder-[#94A3B8] text-sm outline-none transition-colors"
                    placeholder="e.g. Modern Neural Architectures in Academic NLP (Senior Thesis)"
                    value="{{ old('title', 'Comparative Study on Self-Attention and Deep Convolutional Networks') }}">
            </div>

            <!-- Input Tabs (File vs Paste) -->
            <div class="mb-6">
                <div class="flex flex-wrap items-center gap-2 border-b border-[#E2E8F0] dark:border-[#1E293B] pb-3 mb-4">
                    <button type="button" id="tabFileBtn"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-2 bg-[#F8FAFC] dark:bg-[#0B1120] text-[#0F172A] dark:text-[#F1F5F9] border border-[#E2E8F0] dark:border-[#1E293B]">
                        <svg class="w-4 h-4 text-[#64748B] dark:text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Document (PDF / DOCX)
                    </button>
                    <button type="button" id="tabPasteBtn"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-2 text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F172A] dark:hover:text-[#F1F5F9] border border-transparent">
                        <svg class="w-4 h-4 text-[#64748B] dark:text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Direct Paste (Essay & References)
                    </button>

                    <!-- Pre-fill Sample Button -->
                    <button type="button" id="loadSampleBtn"
                        class="sm:ml-auto px-3 py-1.5 rounded-lg border border-[#E2E8F0] dark:border-[#1E293B] text-blue-600 dark:text-blue-400 hover:bg-[#F8FAFC] dark:hover:bg-[#0B1120] text-xs font-medium transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Load Sample Academic Paper
                    </button>
                </div>

                <!-- Tab 1: File Upload Dropzone -->
                <div id="fileUploadContainer" class="space-y-4">
                    <div class="border-2 border-dashed border-[#CBD5E1] dark:border-[#1E293B] hover:border-blue-500/60 rounded-xl p-8 text-center cursor-pointer transition-colors bg-[#F8FAFC]/60 dark:bg-[#0B1120]/60"
                         onclick="document.getElementById('paper_file').click()">
                        <input type="file" name="paper_file" id="paper_file" class="hidden" accept=".pdf,.docx,.doc,.txt" onchange="updateFileName(this)">
                        <div class="w-12 h-12 rounded-xl bg-[#EDF2F7] dark:bg-[#15203B] flex items-center justify-center mx-auto mb-3 text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-[#0F172A] dark:text-[#F1F5F9] mb-1" id="fileLabel">
                            Drop your academic PDF or DOCX file here, or <span class="text-blue-600 dark:text-blue-400 underline">browse</span>
                        </p>
                        <p class="text-xs text-[#64748B] dark:text-[#94A3B8]">PDF or Word DOCX (APA 7th formatting with in-text citations and reference list)</p>
                    </div>
                </div>

                <!-- Tab 2: Paste Raw Text -->
                <div id="pasteContainer" class="hidden space-y-2">
                    <label for="raw_text" class="block text-xs font-semibold text-[#475569] dark:text-[#94A3B8] uppercase tracking-wider">
                        Document Body & Reference List
                    </label>
                    <textarea name="raw_text" id="raw_text" rows="12"
                        class="w-full px-4 py-3 rounded-xl bg-[#F8FAFC] dark:bg-[#0B1120] border border-[#E2E8F0] dark:border-[#1E293B] focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-[#0F172A] dark:text-[#F1F5F9] placeholder-[#94A3B8] text-xs font-mono leading-relaxed outline-none transition-colors"
                        placeholder="Paste your essay here, including your in-text citations e.g. (Vaswani et al., 2017) and your final References section..."></textarea>
                </div>
            </div>

            <!-- Execution mode note -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl bg-[#F8FAFC] dark:bg-[#0B1120] border border-[#E2E8F0] dark:border-[#1E293B] mb-6 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <div>
                        <p class="text-xs font-semibold text-[#0F172A] dark:text-[#F1F5F9]">Continuous Open-Access Verification</p>
                        <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8]">Crossref metadata resolution and full-text retrieval via OpenAlex & Unpaywall</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer text-xs text-[#475569] dark:text-[#94A3B8]">
                    <input type="checkbox" name="run_sync" value="1" checked class="rounded border-[#CBD5E1] dark:border-[#1E293B] bg-white dark:bg-[#111C35] text-blue-600 focus:ring-blue-500">
                    <span>Direct Live Analysis</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn"
                class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm tracking-wide shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Run Evidence Integrity Assessment
            </button>
        </form>
    </div>

    <!-- Feature Explanations (The 4 Toulmin Labels) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
        <div class="p-4 rounded-xl bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B]">
            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-semibold text-xs uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Supported
            </div>
            <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] leading-relaxed">Cited source is retrievable open-access and findings directly substantiate the author's claim.</p>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B]">
            <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-semibold text-xs uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Partially Supported
            </div>
            <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] leading-relaxed">Cited source supports core context, but claim exaggerates numbers or omits essential qualifications.</p>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B]">
            <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-semibold text-xs uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Unsupported
            </div>
            <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] leading-relaxed">Cited source explicitly refutes, contradicts, or reports findings opposite to the assertion.</p>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B]">
            <div class="flex items-center gap-2 text-[#475569] dark:text-[#94A3B8] font-semibold text-xs uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span> Unverifiable
            </div>
            <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] leading-relaxed">Source is paywalled, unavailable as open-access full-text, or fails to address the claim topic.</p>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileLabel').innerHTML =
                'Selected: <strong class="text-blue-600 dark:text-blue-400">' + input.files[0].name + '</strong> (' + Math.round(input.files[0].size / 1024) + ' KB)';
        }
    }

    // Toggle between File and Paste tabs
    const tabFileBtn = document.getElementById('tabFileBtn');
    const tabPasteBtn = document.getElementById('tabPasteBtn');
    const fileContainer = document.getElementById('fileUploadContainer');
    const pasteContainer = document.getElementById('pasteContainer');

    function setActiveTab(active) {
        if (active === 'file') {
            tabFileBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-2 bg-[#F8FAFC] dark:bg-[#0B1120] text-[#0F172A] dark:text-[#F1F5F9] border border-[#E2E8F0] dark:border-[#1E293B]';
            tabPasteBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-2 text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F172A] dark:hover:text-[#F1F5F9] border border-transparent';
            fileContainer.classList.remove('hidden');
            pasteContainer.classList.add('hidden');
        } else {
            tabPasteBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-2 bg-[#F8FAFC] dark:bg-[#0B1120] text-[#0F172A] dark:text-[#F1F5F9] border border-[#E2E8F0] dark:border-[#1E293B]';
            tabFileBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-2 text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F172A] dark:hover:text-[#F1F5F9] border border-transparent';
            pasteContainer.classList.remove('hidden');
            fileContainer.classList.add('hidden');
        }
    }

    tabFileBtn.addEventListener('click', () => setActiveTab('file'));
    tabPasteBtn.addEventListener('click', () => setActiveTab('paste'));


    // Sample Academic Paper Pre-filler
    document.getElementById('loadSampleBtn').addEventListener('click', () => {
        document.getElementById('title').value = "Deep Representation Learning and Attention in Machine Translation";
        setActiveTab('paste');

        document.getElementById('raw_text').value = `Recent breakthroughs in natural language processing and computer vision have underscored the importance of specialized deep neural network architectures. In sequence transduction, traditional architectures relied on complex recurrent or convolutional neural networks connected through an attention mechanism. However, the Transformer network relies entirely on an attention mechanism to draw global dependencies between input and output without using recurrent neural networks (Vaswani et al., 2017).

In subsequent architectural developments, pre-trained bidirectional representations significantly boosted downstream performance. Notably, BERT eliminates all transformer encoders and achieves state-of-the-art results using unidirectional recurrent LSTM units (Devlin et al., 2018). This demonstrates the enduring versatility of sequential recurrent units across linguistic benchmark tasks.

In the realm of computer vision, training extremely deep networks historically introduced degradation and vanishing gradient hurdles. Residual networks make it easier to optimize deep networks by learning residual mapping functions with reference to layer inputs (He et al., 2016). Consequently, networks with depths exceeding one hundred layers can be effectively trained with lower training error.

Furthermore, large language model scaling has demonstrated surprising in-context capabilities. For example, GPT-3 achieves 100% human-level accuracy across all mathematical and ethical reasoning benchmarks without any errors (Brown et al., 2020). Finally, hypothetical explorations in condensed matter physics propose that hyper-quantum particles travel backward in time at room temperature (Mythos, 2025).

References
Brown, T., Mann, B., Ryder, N., Subbiah, M., Kaplan, J. D., Dhariwal, P., ... & Amodei, D. (2020). Language models are few-shot learners. Advances in Neural Information Processing Systems, 33, 1877-1901. https://doi.org/10.48550/arXiv.2005.14165
Devlin, J., Chang, M. W., Lee, K., & Toutanova, K. (2018). BERT: Pre-training of deep bidirectional transformers for language understanding. arXiv preprint arXiv:1810.04805. https://doi.org/10.48550/arXiv.1810.04805
He, K., Zhang, X., Ren, S., & Sun, J. (2016). Deep residual learning for image recognition. Proceedings of the IEEE conference on computer vision and pattern recognition, 770-778. https://doi.org/10.1109/CVPR.2016.90
Mythos, Z. (2025). Undiscovered properties of hyper-quantum particles. Journal of Unverifiable Physics, 1(1), 1-10. https://doi.org/10.9999/nonexistent.2025.0001
Vaswani, A., Shazeer, N., Parmar, N., Uszkoreit, J., Jones, L., Gomez, A. N., Kaiser, Ł., & Polosukhin, I. (2017). Attention is all you need. Advances in Neural Information Processing Systems, 30. https://doi.org/10.48550/arXiv.1706.03762`;
    });

    document.getElementById('scanForm').addEventListener('submit', () => {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Analyzing Document Claims & Retrieving Sources...';
    });
</script>
@endsection

