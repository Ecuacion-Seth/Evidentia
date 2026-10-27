<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'evidence_quote',
        'reasoning_warrant',
        'confidence_score',
        'source_doi',
        'source_location',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
