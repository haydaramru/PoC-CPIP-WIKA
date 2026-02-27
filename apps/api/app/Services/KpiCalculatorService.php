<?php

namespace App\Services;

class KpiCalculatorService
{
    /**
     * Hitung CPI = Planned Cost / Actual Cost
     * (EV = Planned Cost, AC = Actual Cost — sesuai brief CPIP)
     */
    public function calculateCpi(float $plannedCost, float $actualCost): float
    {
        if ($actualCost == 0) return 0;
        return round($plannedCost / $actualCost, 1);
    }

    /**
     * Hitung SPI = Planned Duration / Actual Duration
     */
    public function calculateSpi(int $plannedDuration, int $actualDuration): float
    {
        if ($actualDuration == 0) return 0;
        return round($plannedDuration / $actualDuration, 1);
    }

    /**
     * status project berdasarkan CPI dan SPI
     * Kita hitung dari yang paling kritis dulu (merah), baru yang hijau, sisanya kuning.
     * Hijau   → CPI >= 1 DAN SPI >= 1
     * Merah   → CPI < 0.9 ATAU SPI < 0.9
     * Kuning  → salah satu < 1 (tapi tidak ada yang < 0.9)
     */
    public function determineStatus(float $cpi, float $spi): string
    {
        if ($cpi < 0.9 || $spi < 0.9) {
            return 'critical'; // Merah
        }

        if ($cpi >= 1 && $spi >= 1) {
            return 'good'; // Hijau
        }

        return 'warning';
    }

    /**
     * Hitung semua KPI sekaligus dan return sebagai array
     */
    public function calculate(
        float $plannedCost,
        float $actualCost,
        int $plannedDuration,
        int $actualDuration
    ): array {
        $cpi = $this->calculateCpi($plannedCost, $actualCost);
        $spi = $this->calculateSpi($plannedDuration, $actualDuration);
        $status = $this->determineStatus($cpi, $spi);

        return compact('cpi', 'spi', 'status');
    }
}