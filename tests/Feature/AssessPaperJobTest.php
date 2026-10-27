<?php

namespace Tests\Feature;

use App\Jobs\AssessPaperJob;
use App\Models\Paper;
use App\Services\EvidentiaEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AssessPaperJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_assess_paper_job_invokes_engine_service(): void
    {
        $paper = Paper::create([
            'title' => 'Job Queue Paper',
            'original_filename' => 'job_test.txt',
            'storage_path' => 'papers/job_test.txt',
            'status' => 'pending',
        ]);

        $mockService = Mockery::mock(EvidentiaEngineService::class);
        $mockService->shouldReceive('assessPaper')
            ->once()
            ->with(Mockery::on(fn ($p) => $p->id === $paper->id))
            ->andReturn($paper);

        $job = new AssessPaperJob($paper);
        $job->handle($mockService);

        $this->assertTrue(true);
    }

    public function test_assess_paper_job_marks_paper_failed_on_exception(): void
    {
        $paper = Paper::create([
            'title' => 'Failing Job Paper',
            'original_filename' => 'fail.txt',
            'storage_path' => 'papers/fail.txt',
            'status' => 'pending',
        ]);

        $mockService = Mockery::mock(EvidentiaEngineService::class);
        $mockService->shouldReceive('assessPaper')
            ->once()
            ->andThrow(new \RuntimeException('Engine connection timeout'));

        $this->expectException(\RuntimeException::class);

        $job = new AssessPaperJob($paper);
        try {
            $job->handle($mockService);
        } finally {
            $paper->refresh();
            $this->assertEquals('failed', $paper->status);
            $this->assertStringContainsString('Engine connection timeout', $paper->error_message);
        }
    }
}
