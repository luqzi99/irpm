<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    // Relationships
    public function commentTemplates(): HasMany
    {
        return $this->hasMany(CommentTemplate::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    // Get comment template for specific TP
    public function getTemplateForTp(int $tp): ?CommentTemplate
    {
        return $this->commentTemplates()->where('tp', $tp)->first();
    }
}
