<?php

namespace Tests\Unit;

use App\Models\Claim;
use PHPUnit\Framework\TestCase;

class PaperModelUnitTest extends TestCase
{
    public function test_claim_effective_verdict_uses_override_when_present(): void
    {
        $claim = new Claim([
            'claim_text' => 'Sample test claim',
            'in_text_citation' => '(Author, 2021)',
            'verdict' => 'UNVERIFIABLE',
            'instructor_override_verdict' => 'SUPPORTED',
        ]);

        $this->assertEquals('SUPPORTED', $claim->effective_verdict);
    }

    public function test_claim_effective_verdict_uses_original_when_override_is_null(): void
    {
        $claim = new Claim([
            'claim_text' => 'Sample test claim',
            'in_text_citation' => '(Author, 2021)',
            'verdict' => 'PARTIALLY_SUPPORTED',
            'instructor_override_verdict' => null,
        ]);

        $this->assertEquals('PARTIALLY_SUPPORTED', $claim->effective_verdict);
    }
}
