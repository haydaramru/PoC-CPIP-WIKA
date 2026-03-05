<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\KpiCalculatorService;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'project_name',
        'division',
        'owner',
        'contract_value',
        'planned_cost',
        'actual_cost',
        'planned_duration',
        'actual_duration',
        'progress_pct',
        'project_year',
        'cpi',
        'spi',
        'status',
        'ingestion_file_id',
    ];

    protected $casts = [
        'contract_value'  => 'decimal:2',
        'planned_cost'    => 'decimal:2',
        'actual_cost'     => 'decimal:2',
        'progress_pct'    => 'decimal:2',
        'project_year' => 'integer',
        'cpi'             => 'decimal:1',
        'spi'             => 'decimal:1',
        'planned_duration'=> 'integer',
        'actual_duration' => 'integer',
    ];

    
    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->project_year)) {
                $project->project_year = (int) now()->format('Y');
            }

            $kpi = (new KpiCalculatorService())->calculate(
                (float) $project->planned_cost,
                (float) $project->actual_cost,
                (int)   $project->planned_duration,
                (int)   $project->actual_duration,
            );

            $project->cpi    = $kpi['cpi'];
            $project->spi    = $kpi['spi'];
            $project->status = $kpi['status'];
        });
    }

    
    public function scopeByDivision($query, ?string $division)
    {
        if ($division) {
            return $query->where('division', $division);
        }
        return $query;
    }

    public function scopeByContractRange($query, ?float $min, ?float $max)
    {
        if ($min !== null) $query->where('contract_value', '>=', $min);
        if ($max !== null) $query->where('contract_value', '<=', $max);
        return $query;
    }

    public function scopeByStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }
    
    public function scopeByYear($query, ?int $year)
    {
        if ($year) {
            return $query->where('project_year', $year);
        }
        return $query;
    }

   
    public function getIsOverbudgetAttribute(): bool
    {
        return $this->cpi < 1;
    }

    public function getIsDelayAttribute(): bool
    {
        return $this->spi < 1;
    }
}