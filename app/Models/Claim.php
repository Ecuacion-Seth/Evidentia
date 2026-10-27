<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'paper_id',
        'paper_reference_id',
        'claim_text',
        'in_text_citation',
        'paragraph_index',
        'verdict',
        'instructor_override_verdict',
        'instructor_notes',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(PaperReference::class, 'paper_reference_id');
    }

    public function evidence(): HasOne
    {
        return $this->hasOne(EvidenceMatch::class);
    }

    public function getEffectiveVerdictAttribute(): string
    {
        return $this->instructor_override_verdict ?: $this->verdict;
    }
}
