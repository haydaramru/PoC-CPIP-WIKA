<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgressCurve extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'week_number',
        'week_date',
        'rencana_pct',
        'realisasi_pct',
        'deviasi_pct',
        'keterangan',
    ];

    protected $casts = [
        'week_number'   => 'integer',
        'week_date'     => 'date',
        'rencana_pct'   => 'decimal:2',
        'realisasi_pct' => 'decimal:2',
        'deviasi_pct'   => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
