<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVendor extends Model
{
    protected $fillable = [
        'project_id', 'ingestion_file_id', 'vendor_name', 'npwp', 'lokasi',
        'po_number', 'po_date', 'contract_value', 'uang_muka', 'termin_paid',
        'retensi', 'ppn_status', 'currency',
    ];

    protected $casts = [
        'po_date'        => 'date',
        'contract_value' => 'decimal:2',
        'uang_muka'      => 'decimal:2',
        'termin_paid'    => 'decimal:2',
        'retensi'        => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
