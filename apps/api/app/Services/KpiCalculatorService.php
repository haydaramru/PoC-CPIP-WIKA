<?php

namespace App\Services;

class KpiCalculatorService
{
    public function calculateCpi(float $plannedCost, float $actualCost): float
    {
        if ($actualCost == 0) return 0.0;
        return round($plannedCost / $actualCost, 1);
    }

    public function calculateSpi(int $plannedDuration, int $actualDuration): float
    {
        if ($actualDuration == 0) return 0.0;
        return round($plannedDuration / $actualDuration, 1);
    }

    public function determineStatus(float $cpi, float $spi): string
    {
        if ($cpi < 0.9 || $spi < 0.9) {
            return 'critical';
        }

        if ($cpi >= 1 && $spi >= 1) {
            return 'good';
        }

        return 'warning';
    }

    public function calculate(
        float $plannedCost,
        float $actualCost,
        int $plannedDuration,
        int $actualDuration
    ): array {
        $cpi    = $this->calculateCpi($plannedCost, $actualCost);
        $spi    = $this->calculateSpi($plannedDuration, $actualDuration);
        $status = $this->determineStatus($cpi, $spi);

        return compact('cpi', 'spi', 'status');
    }
}