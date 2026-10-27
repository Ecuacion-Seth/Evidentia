<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'storage_path',
        'document_text',
        'status',
        'error_message',
        'practical_coverage',
        'ai_supplementary_score',
        'ai_disclaimer',
    ];

    protected function casts(): array
    {
        return [
            'practical_coverage' => 'float',
            'ai_supplementary_score' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(PaperReference::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function getVerdictStatsAttribute(): array
    {
        return [
            'SUPPORTED' => $this->claims()->where('verdict', 'SUPPORTED')->count(),
            'PARTIALLY_SUPPORTED' => $this->claims()->where('verdict', 'PARTIALLY_SUPPORTED')->count(),
            'UNSUPPORTED' => $this->claims()->where('verdict', 'UNSUPPORTED')->count(),
            'UNVERIFIABLE' => $this->claims()->where('verdict', 'UNVERIFIABLE')->count(),
        ];
    }
}
