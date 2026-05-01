<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProfitLoss extends Model
{
    use HasFactory;

    protected $table = 'project_profit_loss';

    protected $fillable = [
        'project_id',
        'beban_pph_final',
        'laba_kotor',
        'lsp',
    ];

    protected $casts = [
        'beban_pph_final' => 'decimal:2',
        'laba_kotor' => 'decimal:2',
        'lsp' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
