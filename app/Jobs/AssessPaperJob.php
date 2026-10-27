<?php

namespace App\Jobs;

use App\Models\Paper;
use App\Services\EvidentiaEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AssessPaperJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Paper $paper
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EvidentiaEngineService $engineService): void
    {
        Log::info("Starting Evidentia assessment for Paper #{$this->paper->id}: '{$this->paper->title}'");

        try {
            $engineService->assessPaper($this->paper);
            Log::info("Evidentia assessment completed successfully for Paper #{$this->paper->id}");
        } catch (\Throwable $e) {
            Log::error("Evidentia assessment failed for Paper #{$this->paper->id}: {$e->getMessage()}");
            $this->paper->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
