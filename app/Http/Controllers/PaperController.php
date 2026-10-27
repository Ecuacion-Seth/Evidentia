<?php

namespace App\Http\Controllers;

use App\Jobs\AssessPaperJob;
use App\Models\Claim;
use App\Models\Paper;
use App\Services\EvidentiaEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaperController extends Controller
{
    /**
     * Display a listing of papers (Dashboard / Scan history).
     */
    public function index(Request $request): View
    {
        $papers = Paper::with(['claims', 'references'])
            ->latest()
            ->paginate(15);

        return view('papers.index', compact('papers'));
    }

    /**
     * Show the upload & assessment form.
     */
    public function create(): View
    {
        return view('papers.create');
    }

    /**
     * Store a newly submitted paper and trigger assessment.
     */
    public function store(Request $request, EvidentiaEngineService $engineService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'paper_file' => 'nullable|file|mimes:pdf,docx,doc,txt|max:25600',
            'raw_text' => 'nullable|string',
            'run_sync' => 'nullable|boolean',
        ]);

        if (! $request->hasFile('paper_file') && empty($validated['raw_text'])) {
            return back()->withErrors(['paper_file' => 'Please provide a PDF/DOCX file or paste the document text.'])->withInput();
        }

        if ($request->hasFile('paper_file')) {
            $file = $request->file('paper_file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('papers');
        } else {
            $originalName = 'pasted_submission.txt';
            $path = 'papers/'.uniqid('text_', true).'.txt';
            Storage::put($path, $validated['raw_text']);
        }

        $paper = Paper::create([
            'user_id' => $request->user()?->id,
            'title' => $validated['title'],
            'original_filename' => $originalName,
            'storage_path' => $path,
            'status' => 'pending',
        ]);

        // If requested to run synchronously (or default in local demo without running worker), process directly
        if ($request->boolean('run_sync', true)) {
            try {
                $engineService->assessPaper($paper);
            } catch (\Throwable $e) {
                // Assessment logged error on paper record
            }
        } else {
            AssessPaperJob::dispatch($paper);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Paper submitted for evidence integrity assessment.',
                'paper_id' => $paper->id,
                'redirect_url' => route('papers.show', $paper),
            ]);
        }

        return redirect()->route('papers.show', $paper);
    }

    /**
     * Display the Evidence Integrity Report (Interactive Split-Screen).
     */
    public function show(Paper $paper): View
    {
        $paper->load(['references', 'claims.evidence', 'claims.reference']);

        // Group claims by paragraph index
        $claimsByParagraph = $paper->claims->groupBy('paragraph_index');

        // Verdict distribution summary
        $stats = [
            'total_claims' => $paper->claims->count(),
            'supported' => $paper->claims->where('verdict', 'SUPPORTED')->count(),
            'partially_supported' => $paper->claims->where('verdict', 'PARTIALLY_SUPPORTED')->count(),
            'unsupported' => $paper->claims->where('verdict', 'UNSUPPORTED')->count(),
            'unverifiable' => $paper->claims->where('verdict', 'UNVERIFIABLE')->count(),
        ];

        return view('papers.show', compact('paper', 'claimsByParagraph', 'stats'));
    }

    /**
     * Status polling endpoint for background job completion.
     */
    public function status(Paper $paper): JsonResponse
    {
        return response()->json([
            'id' => $paper->id,
            'status' => $paper->status,
            'practical_coverage' => $paper->practical_coverage,
            'claims_count' => $paper->claims()->count(),
            'error_message' => $paper->error_message,
        ]);
    }

    /**
     * Instructor manual review override for a claim.
     */
    public function overrideClaim(Request $request, Claim $claim): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'instructor_override_verdict' => 'required|in:SUPPORTED,PARTIALLY_SUPPORTED,UNSUPPORTED,UNVERIFIABLE',
            'instructor_notes' => 'nullable|string|max:1000',
        ]);

        $claim->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Verdict overridden by instructor.',
                'claim' => $claim->fresh(['evidence', 'reference']),
            ]);
        }

        return back()->with('status', 'Instructor verdict saved.');
    }

    /**
     * Export complete integrity report as JSON.
     */
    public function exportJson(Paper $paper): JsonResponse
    {
        $paper->load(['references', 'claims.evidence', 'claims.reference']);

        return response()->json([
            'evidentia_version' => '1.0.0',
            'assessment_timestamp' => now()->toIso8601String(),
            'paper' => [
                'id' => $paper->id,
                'title' => $paper->title,
                'original_filename' => $paper->original_filename,
                'status' => $paper->status,
                'practical_coverage_percent' => $paper->practical_coverage,
                'ai_supplementary_indicator' => [
                    'score' => $paper->ai_supplementary_score,
                    'disclaimer' => $paper->ai_disclaimer,
                ],
            ],
            'cited_references' => $paper->references->map(fn ($r) => [
                'citation_key' => $r->citation_key,
                'authors' => $r->authors,
                'year' => $r->year,
                'title' => $r->title,
                'doi' => $r->doi,
                'open_access_url' => $r->open_access_url,
                'retrieval_status' => $r->retrieval_status,
                'raw_text' => $r->raw_text,
            ]),
            'verified_claims' => $paper->claims->map(fn ($c) => [
                'id' => $c->id,
                'claim_text' => $c->claim_text,
                'in_text_citation' => $c->in_text_citation,
                'verdict' => $c->verdict,
                'instructor_override' => $c->instructor_override_verdict,
                'instructor_notes' => $c->instructor_notes,
                'toulmin_evidence' => [
                    'grounds_quote' => $c->evidence?->evidence_quote,
                    'warrant_reasoning' => $c->evidence?->reasoning_warrant,
                    'confidence' => $c->evidence?->confidence_score,
                    'source_doi' => $c->evidence?->source_doi,
                ],
            ]),
        ]);
    }

    /**
     * Re-run assessment.
     */
    public function reassess(Paper $paper, EvidentiaEngineService $engineService): RedirectResponse
    {
        try {
            $engineService->assessPaper($paper);

            return back()->with('status', 'Paper reassessment completed.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Reassessment failed: '.$e->getMessage()]);
        }
    }
}
