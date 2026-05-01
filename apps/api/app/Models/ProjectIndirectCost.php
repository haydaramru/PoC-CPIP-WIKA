<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectIndirectCost extends Model
{
    use HasFactory;

    protected $table = 'project_indirect_cost';

    protected $fillable = [
        'project_id',
        'fasilitas',
        'sekretariat',
        'kendaraan',
        'personalia',
        'keuangan',
        'umum',
    ];

    protected $casts = [
        'fasilitas' => 'decimal:2',
        'sekretariat' => 'decimal:2',
        'kendaraan' => 'decimal:2',
        'personalia' => 'decimal:2',
        'keuangan' => 'decimal:2',
        'umum' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
