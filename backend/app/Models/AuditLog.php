<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Static helper to log actions
    public static function log(
        int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
        ]);
    }

    // Common action types
    public const ACTION_VIEW_STUDENT = 'view_student';
    public const ACTION_RECORD_TP = 'record_tp';
    public const ACTION_EXPORT_REPORT = 'export_report';
    public const ACTION_IMPORT_DSKP = 'import_dskp';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
}
