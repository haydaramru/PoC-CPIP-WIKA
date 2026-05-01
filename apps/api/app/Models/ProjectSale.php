<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSale extends Model
{
    use HasFactory;

    protected $table = 'project_sales';

    protected $fillable = [
        'project_id',
        'penjualan',
    ];

    protected $casts = [
        'penjualan' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
