<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectOtherCost extends Model
{
    use HasFactory;

    protected $table = 'project_other_cost';

    protected $fillable = [
        'project_id',
        'biaya_pemeliharaan',
        'risiko',
    ];

    protected $casts = [
        'biaya_pemeliharaan' => 'decimal:2',
        'risiko' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
