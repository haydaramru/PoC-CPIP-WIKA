<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectOtherCostItem extends Model
{
    protected $table = 'project_other_cost_items';

    protected $fillable = [
        'project_id', 'ingestion_file_id', 'kategori', 'item', 'nilai', 'catatan',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
