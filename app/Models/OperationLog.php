<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    protected $fillable = [
        'operation_result_id',
        'level',
        'message',
        'context',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Get the operation result this log belongs to.
     */
    public function operationResult(): BelongsTo
    {
        return $this->belongsTo(OperationResult::class);
    }

    /**
     * Get log level badge class.
     */
    public function getLevelBadgeClassAttribute(): string
    {
        return match($this->level) {
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'debug' => 'secondary',
            default => 'secondary',
        };
    }
}
