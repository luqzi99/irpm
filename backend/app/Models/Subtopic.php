<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'code',
        'description',
        'sequence',
        'tp_descriptions',
        'tp_max',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'tp_descriptions' => 'array',
        'tp_max' => 'integer',
    ];

    // Relationships
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    // Get full path: Subject > Topic > Subtopic
    public function getFullPathAttribute(): string
    {
        return "{$this->topic->subject->name} > {$this->topic->title} > {$this->code}";
    }
}
