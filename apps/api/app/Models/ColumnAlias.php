<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColumnAlias extends Model
{
    use HasFactory;

    protected $fillable = [
        'alias',
        'target_field',
        'context',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function resolveAlias(string $normalizedHeader, ?string $context = null): ?string
    {
        return static::where('alias', $normalizedHeader)
            ->where('is_active', true)
            ->where(fn($q) => $q->where('context', $context)->orWhereNull('context'))
            ->orderByRaw('context IS NULL ASC') // context-specific takes priority
            ->value('target_field');
    }

    public static function allForContext(?string $context = null): array
    {
        return static::where('is_active', true)
            ->where(fn($q) => $q->where('context', $context)->orWhereNull('context'))
            ->pluck('target_field', 'alias')
            ->toArray();
    }
}
