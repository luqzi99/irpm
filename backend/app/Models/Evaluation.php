<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'topic_id',
        'subtopic_id',
        'assessment_method_id',
        'tp',
        'auto_comment',
        'custom_comment',
        'evaluation_date',
    ];

    protected $casts = [
        'tp' => 'integer',
        'evaluation_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class);
    }

    public function assessmentMethod(): BelongsTo
    {
        return $this->belongsTo(AssessmentMethod::class);
    }

    // Get the effective comment (custom if set, otherwise auto)
    public function getCommentAttribute(): ?string
    {
        return $this->custom_comment ?? $this->auto_comment;
    }

    // TP color for UI
    public function getTpColorAttribute(): string
    {
        return match($this->tp) {
            1 => 'red',
            2 => 'orange',
            3 => 'yellow',
            4 => 'lime',
            5 => 'green',
            6 => 'emerald',
            default => 'gray',
        };
    }
}
