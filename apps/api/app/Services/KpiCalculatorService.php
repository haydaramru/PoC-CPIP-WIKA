<?php

namespace App\Services;

class KpiCalculatorService
{
    /**
     * Calculate CPI, SPI, and status for a project.
     *
     * Returns null for CPI/SPI when the required inputs are unavailable,
     * which is more honest than returning 0 for "no data".
     */
    public function calculate(
        ?float $plannedCost,
        ?float $actualCost,
        ?int   $plannedDuration,
        ?int   $actualDuration,
        ?float $progressPct = 100.0,
    ): array {
        $cpi = $this->calculateCpi($plannedCost, $actualCost, $progressPct);
        $spi = $this->calculateSpi($plannedDuration, $actualDuration);

        return [
            'cpi'    => $cpi,
            'spi'    => $spi,
            'status' => $this->determineStatus($cpi, $spi),
        ];
    }

    // KPI values outside this range indicate a data quality issue (e.g. mismatched
    // units between planned and actual fields). We store null rather than a
    // nonsensical number that would also overflow narrow decimal columns.
    private const MAX_REASONABLE_KPI = 1000.0;

    public function calculateCpi(?float $plannedCost, ?float $actualCost, ?float $progressPct): ?float
    {
        if ($plannedCost === null || $actualCost === null || $progressPct === null) {
            return null;
        }

        if ($actualCost == 0) {
            return null;
        }

        $earnedValue = ($progressPct / 100) * $plannedCost;
        $cpi = round($earnedValue / $actualCost, 4);

        return abs($cpi) <= self::MAX_REASONABLE_KPI ? $cpi : null;
    }

    public function calculateSpi(?int $plannedDuration, ?int $actualDuration): ?float
    {
        if ($plannedDuration === null || $actualDuration === null) {
            return null;
        }

        if ($actualDuration == 0) {
            return null;
        }

        $spi = round($plannedDuration / $actualDuration, 4);

        return abs($spi) <= self::MAX_REASONABLE_KPI ? $spi : null;
    }

    public function determineStatus(?float $cpi, ?float $spi): string
    {
        // Cannot determine status without KPI values
        if ($cpi === null || $spi === null) {
            return 'unknown';
        }

        if ($cpi < 0.9 || $spi < 0.9) {
            return 'critical';
        }

        if ($cpi >= 1.0 && $spi >= 1.0) {
            return 'good';
        }

        return 'warning';
    }
}
