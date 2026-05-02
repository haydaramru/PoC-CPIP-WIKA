<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectIndirectCostItem extends Model
{
    protected $table = 'project_indirect_cost_items';

    protected $fillable = [
        'project_id', 'ingestion_file_id', 'sub_kategori', 'item_detail',
        'budget', 'realisasi', 'deviasi', 'catatan',
    ];

    protected $casts = [
        'budget'    => 'decimal:2',
        'realisasi' => 'decimal:2',
        'deviasi'   => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
