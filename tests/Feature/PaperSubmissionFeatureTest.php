<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\EvidenceMatch;
use App\Models\Paper;
use App\Models\PaperReference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaperSubmissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_paper_submission_form(): void
    {
        $response = $this->get(route('papers.create'));

        $response->assertStatus(200);
        $response->assertSee('Verify Whether Sources');
        $response->assertSee('Toulmin-Grounded Academic Evidence Integrity');
    }

    public function test_paper_submission_validation(): void
    {
        $response = $this->post(route('papers.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_can_view_paper_integrity_report_with_toulmin_data(): void
    {
        $paper = Paper::create([
            'title' => 'Attention and Neural Alignment',
            'original_filename' => 'test_essay.docx',
            'storage_path' => 'papers/test.txt',
            'document_text' => 'The Transformer relies entirely on attention (Vaswani et al., 2017).',
            'status' => 'completed',
            'practical_coverage' => 100.0,
            'ai_supplementary_score' => 45.0,
            'ai_disclaimer' => 'Supplementary mathematical indicator.',
        ]);

        $reference = PaperReference::create([
            'paper_id' => $paper->id,
            'citation_key' => 'Vaswani et al., 2017',
            'raw_text' => 'Vaswani, A., et al. (2017). Attention is all you need.',
            'title' => 'Attention is all you need',
            'doi' => '10.48550/arXiv.1706.03762',
            'open_access_url' => 'https://arxiv.org/pdf/1706.03762',
            'retrieval_status' => 'RETRIEVED',
        ]);

        $claim = Claim::create([
            'paper_id' => $paper->id,
            'paper_reference_id' => $reference->id,
            'claim_text' => 'The Transformer relies entirely on attention (Vaswani et al., 2017).',
            'in_text_citation' => '(Vaswani et al., 2017)',
            'paragraph_index' => 0,
            'verdict' => 'SUPPORTED',
        ]);

        EvidenceMatch::create([
            'claim_id' => $claim->id,
            'evidence_quote' => 'The Transformer is the first transduction model relying entirely on self-attention.',
            'reasoning_warrant' => 'The cited source directly verifies the claim.',
            'confidence_score' => 0.95,
            'source_doi' => '10.48550/arXiv.1706.03762',
        ]);

        $response = $this->get(route('papers.show', $paper));

        $response->assertStatus(200);
        $response->assertSee('Attention and Neural Alignment');
        $response->assertSee('100.0% OA');
        $response->assertSee('SUPPORTED');
        $response->assertSee('Grounds: Retrieved Evidence Quote');
    }

    public function test_can_poll_paper_status_api(): void
    {
        $paper = Paper::create([
            'title' => 'Processing Paper',
            'original_filename' => 'sample.pdf',
            'storage_path' => 'papers/sample.pdf',
            'status' => 'completed',
            'practical_coverage' => 75.5,
        ]);

        $response = $this->getJson(route('papers.status', $paper));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $paper->id,
            'status' => 'completed',
            'practical_coverage' => 75.5,
        ]);
    }

    public function test_instructor_can_override_claim_verdict(): void
    {
        $paper = Paper::create([
            'title' => 'Sample Paper for Override',
            'original_filename' => 'doc.docx',
            'storage_path' => 'papers/doc.docx',
            'status' => 'completed',
        ]);

        $claim = Claim::create([
            'paper_id' => $paper->id,
            'claim_text' => 'A controversial claim requiring manual check.',
            'in_text_citation' => '(Smith, 2020)',
            'verdict' => 'UNVERIFIABLE',
        ]);

        $response = $this->postJson(route('claims.override', $claim), [
            'instructor_override_verdict' => 'SUPPORTED',
            'instructor_notes' => 'Confirmed in physical textbook edition page 42.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $claim->refresh();
        $this->assertEquals('SUPPORTED', $claim->instructor_override_verdict);
        $this->assertEquals('SUPPORTED', $claim->effective_verdict);
        $this->assertEquals('Confirmed in physical textbook edition page 42.', $claim->instructor_notes);
    }

    public function test_can_export_report_as_json(): void
    {
        $paper = Paper::create([
            'title' => 'JSON Export Paper',
            'original_filename' => 'export.docx',
            'storage_path' => 'papers/export.docx',
            'status' => 'completed',
            'practical_coverage' => 90.0,
        ]);

        $response = $this->getJson(route('papers.export.json', $paper));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'evidentia_version',
            'assessment_timestamp',
            'paper' => ['id', 'title', 'status', 'practical_coverage_percent'],
            'cited_references',
            'verified_claims',
        ]);
    }
}
