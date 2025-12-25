<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DskpImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'dskp_import_id',
        'row_number',
        'message',
        'level',
    ];

    protected $casts = [
        'row_number' => 'integer',
    ];

    // Relationships
    public function dskpImport(): BelongsTo
    {
        return $this->belongsTo(DskpImport::class);
    }

    // Level helpers
    public function isError(): bool
    {
        return $this->level === 'error';
    }

    public function isWarning(): bool
    {
        return $this->level === 'warning';
    }

    public function isInfo(): bool
    {
        return $this->level === 'info';
    }
}
