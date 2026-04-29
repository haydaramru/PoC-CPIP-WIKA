<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinancialSummary extends Model
{
    use HasFactory;

    protected $table = 'project_financial_summary';

    protected $fillable = [
        'project_id',
        'penjualan',
        'material',
        'upah',
        'alat',
        'subkon',
        'fasilitas',
        'sekretariat',
        'kendaraan',
        'personalia',
        'keuangan',
        'umum',
        'biaya_pemeliharaan',
        'risiko',
        'beban_pph_final',
        'laba_kotor',
        'lsp',
    ];

    protected $casts = [
        'penjualan' => 'decimal:2',
        'material' => 'decimal:2',
        'upah' => 'decimal:2',
        'alat' => 'decimal:2',
        'subkon' => 'decimal:2',
        'fasilitas' => 'decimal:2',
        'sekretariat' => 'decimal:2',
        'kendaraan' => 'decimal:2',
        'personalia' => 'decimal:2',
        'keuangan' => 'decimal:2',
        'umum' => 'decimal:2',
        'biaya_pemeliharaan' => 'decimal:2',
        'risiko' => 'decimal:2',
        'beban_pph_final' => 'decimal:2',
        'laba_kotor' => 'decimal:2',
        'lsp' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
