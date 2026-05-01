<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDirectCost extends Model
{
    use HasFactory;

    protected $table = 'project_direct_cost';

    protected $fillable = [
        'project_id',
        'material',
        'upah',
        'alat',
        'subkon',
    ];

    protected $casts = [
        'material' => 'decimal:2',
        'upah' => 'decimal:2',
        'alat' => 'decimal:2',
        'subkon' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
