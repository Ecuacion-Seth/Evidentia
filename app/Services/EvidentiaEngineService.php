<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\EvidenceMatch;
use App\Models\Paper;
use App\Models\PaperReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class EvidentiaEngineService
{
    public function __construct(
        protected string $engineUrl = 'http://127.0.0.1:8001'
    ) {}

    /**
     * Executes assessment pipeline on a Paper instance.
     *
     * @throws \RuntimeException
     */
    public function assessPaper(Paper $paper): Paper
    {
        $paper->update(['status' => 'processing']);

        $fullPath = storage_path('app/private/'.$paper->storage_path);
        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/'.$paper->storage_path);
        }

        if (! file_exists($fullPath)) {
            $paper->update([
                'status' => 'failed',
                'error_message' => "Paper file not found on disk at: {$fullPath}",
            ]);
            throw new \RuntimeException("Paper file not found: {$fullPath}");
        }

        $fullPath = realpath($fullPath) ?: str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
        $resultData = null;

        // Try Python FastAPI microservice first
        try {
            $response = Http::timeout(120)->post("{$this->engineUrl}/api/v1/assess-file", [
                'file_path' => $fullPath,
                'title' => $paper->title,
            ]);

            if ($response->successful()) {
                $resultData = $response->json();
            }
        } catch (\Throwable $e) {
            Log::info("Evidentia FastAPI microservice unreachable; falling back to direct CLI execution: {$e->getMessage()}");
        }

        // Direct Process CLI fallback if microservice is offline
        if (! $resultData) {
            $scriptPath = realpath(base_path('python-engine/main_cli.py')) ?: str_replace('/', DIRECTORY_SEPARATOR, base_path('python-engine/main_cli.py'));
            $command = ['python', $scriptPath, $fullPath, $paper->title];

            $processResult = Process::timeout(120)->run($command);

            if (! $processResult->successful()) {
                $err = $processResult->errorOutput() ?: $processResult->output();
                $paper->update([
                    'status' => 'failed',
                    'error_message' => "Python engine execution error: {$err}",
                ]);
                throw new \RuntimeException("Python engine failed: {$err}");
            }

            $output = trim($processResult->output());
            $resultData = json_decode($output, true);

            if (! $resultData || isset($resultData['error'])) {
                $err = $resultData['error'] ?? 'Invalid JSON output from Python engine';
                $paper->update(['status' => 'failed', 'error_message' => $err]);
                throw new \RuntimeException($err);
            }
        }

        // Persist assessment results transactionally
        DB::transaction(function () use ($paper, $resultData) {
            $aiData = $resultData['ai_indicator'] ?? [];

            $paper->update([
                'status' => 'completed',
                'document_text' => $resultData['body_text'] ?? null,
                'practical_coverage' => $resultData['practical_coverage'] ?? 0.0,
                'ai_supplementary_score' => $aiData['score'] ?? null,
                'ai_disclaimer' => $aiData['disclaimer'] ?? null,
            ]);

            // Clear any previous references/claims if re-assessing
            $paper->references()->delete();
            $paper->claims()->delete();

            $savedReferences = [];
            foreach ($resultData['references'] ?? [] as $ref) {
                $savedRef = PaperReference::create([
                    'paper_id' => $paper->id,
                    'citation_key' => mb_substr($ref['citation_key'] ?? 'Unknown', 0, 250),
                    'raw_text' => $ref['raw_text'] ?? '',
                    'authors' => $ref['authors'] ?? null,
                    'year' => ! empty($ref['year']) ? mb_substr($ref['year'], 0, 50) : null,
                    'title' => $ref['title'] ?? null,
                    'journal_or_publisher' => $ref['source'] ?? null,
                    'doi' => ! empty($ref['doi']) ? mb_substr($ref['doi'], 0, 250) : null,
                    'open_access_url' => $ref['open_access_url'] ?? null,
                    'retrieval_status' => $ref['retrieval_status'] ?? 'NOT_FOUND',
                    'cached_full_text_path' => $ref['cached_full_text_path'] ?? null,
                ]);

                $savedReferences[$savedRef->citation_key] = $savedRef;
            }

            foreach ($resultData['claims'] ?? [] as $claim) {
                $citKey = $claim['citation_key'] ?? '';
                $refModel = $savedReferences[$citKey] ?? null;

                // Fallback: match by author surname if exact key differs
                if (! $refModel) {
                    $firstWord = explode(' ', $citKey)[0] ?? '';
                    foreach ($savedReferences as $k => $candidate) {
                        if (str_contains(strtolower($k), strtolower($firstWord))) {
                            $refModel = $candidate;
                            break;
                        }
                    }
                }

                $claimModel = Claim::create([
                    'paper_id' => $paper->id,
                    'paper_reference_id' => $refModel?->id,
                    'claim_text' => $claim['claim_text'],
                    'in_text_citation' => mb_substr($claim['in_text_citation'], 0, 250),
                    'paragraph_index' => $claim['paragraph_index'] ?? 0,
                    'verdict' => $claim['verdict'] ?? 'UNVERIFIABLE',
                ]);

                if (! empty($claim['evidence_quote']) || ! empty($claim['reasoning_warrant'])) {
                    EvidenceMatch::create([
                        'claim_id' => $claimModel->id,
                        'evidence_quote' => $claim['evidence_quote'] ?? null,
                        'reasoning_warrant' => $claim['reasoning_warrant'] ?? null,
                        'confidence_score' => $claim['confidence_score'] ?? 0.0,
                        'source_doi' => $claim['source_doi'] ?? null,
                        'source_location' => 'Open-Access Candidate Segment',
                    ]);
                }
            }
        });

        return $paper->fresh(['references', 'claims.evidence', 'claims.reference']);
    }
}
