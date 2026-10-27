<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'paper_id',
        'citation_key',
        'raw_text',
        'authors',
        'year',
        'title',
        'journal_or_publisher',
        'doi',
        'open_access_url',
        'retrieval_status',
        'cached_full_text_path',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
