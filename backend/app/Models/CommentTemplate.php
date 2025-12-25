<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_method_id',
        'tp',
        'template_text',
    ];

    protected $casts = [
        'tp' => 'integer',
    ];

    // Relationships
    public function assessmentMethod(): BelongsTo
    {
        return $this->belongsTo(AssessmentMethod::class);
    }

    // Generate comment with optional student name placeholder
    public function generateComment(?string $studentName = null): string
    {
        $comment = $this->template_text;
        
        if ($studentName) {
            $comment = str_replace('{murid}', $studentName, $comment);
        }
        
        return $comment;
    }
}
