<?php

namespace Tests\Unit;

use App\Services\KpiCalculatorService;
use PHPUnit\Framework\TestCase;

class KpiCalculatorServiceTest extends TestCase
{
    private KpiCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KpiCalculatorService();
    }

    // =========================================================================
    // CPI Tests
    // =========================================================================

    /** @test */
    public function it_calculates_cpi_correctly(): void
    {
        // CPI = planned_cost / actual_cost = 780 / 910
        $cpi = $this->service->calculateCpi(780, 910);
        $this->assertEqualsWithDelta(0.9, $cpi, 0.0001);
    }

    /** @test */
    public function it_returns_cpi_above_one_when_under_budget(): void
    {
        // planned > actual → under budget → CPI > 1
        $cpi = $this->service->calculateCpi(270, 255);
        $this->assertGreaterThan(1, $cpi);
        $this->assertEqualsWithDelta(1.1, $cpi, 0.0001);
    }

    /** @test */
    public function it_returns_zero_cpi_when_actual_cost_is_zero(): void
    {
        $cpi = $this->service->calculateCpi(500, 0);
        $this->assertEquals(0, $cpi);
    }

    /** @test */
    public function it_returns_one_cpi_when_on_budget(): void
    {
        $cpi = $this->service->calculateCpi(500, 500);
        $this->assertEquals(1.0, $cpi);
    }

    // =========================================================================
    // SPI Tests
    // =========================================================================

    /** @test */
    public function it_calculates_spi_correctly(): void
    {
        // SPI = planned_duration / actual_duration = 24 / 28
        $spi = $this->service->calculateSpi(24, 28);
        $this->assertEqualsWithDelta(0.9, $spi, 0.0001);
    }

    /** @test */
    public function it_returns_spi_above_one_when_ahead_of_schedule(): void
    {
        // planned > actual → ahead of schedule → SPI > 1
        $spi = $this->service->calculateSpi(30, 29);
        $this->assertGreaterThan(1.0, $spi);
        $this->assertEqualsWithDelta(1.0, $spi, 0.0001);
    }

    /** @test */
    public function it_returns_zero_spi_when_actual_duration_is_zero(): void
    {
        $spi = $this->service->calculateSpi(24, 0);
        $this->assertEquals(0, $spi);
    }

    /** @test */
    public function it_returns_one_spi_when_on_schedule(): void
    {
        $spi = $this->service->calculateSpi(14, 14);
        $this->assertEquals(1.0, $spi);
    }

    // =========================================================================
    // Status Tests
    // =========================================================================

    /** @test */
    public function it_returns_good_when_both_cpi_and_spi_are_one_or_above(): void
    {
        $this->assertEquals('good', $this->service->determineStatus(1.0, 1.0));
        $this->assertEquals('good', $this->service->determineStatus(1.2, 1.1));
        $this->assertEquals('good', $this->service->determineStatus(1.059, 1.0)); // Gedung BUMN
    }

    /** @test */
    public function it_returns_critical_when_cpi_is_below_0_9(): void
    {
        $this->assertEquals('critical', $this->service->determineStatus(0.89, 1.0));
        $this->assertEquals('critical', $this->service->determineStatus(0.857, 0.857)); // Tol Semarang
        $this->assertEquals('critical', $this->service->determineStatus(0.5, 0.5));
    }

    /** @test */
    public function it_returns_critical_when_spi_is_below_0_9(): void
    {
        $this->assertEquals('critical', $this->service->determineStatus(1.0, 0.89));
        $this->assertEquals('critical', $this->service->determineStatus(0.872, 0.818)); // RS Surabaya
    }

    /** @test */
    public function it_returns_warning_when_one_value_is_between_0_9_and_1(): void
    {
        // CPI < 1 tapi >= 0.9, SPI >= 1
        $this->assertEquals('warning', $this->service->determineStatus(0.95, 1.0));

        // SPI < 1 tapi >= 0.9, CPI >= 1
        $this->assertEquals('warning', $this->service->determineStatus(1.0, 0.95));

        // Bendungan Citarum: CPI=0.967, SPI=1.034
        $this->assertEquals('warning', $this->service->determineStatus(0.967, 1.034));
    }

    /** @test */
    public function it_returns_warning_when_both_are_between_0_9_and_1(): void
    {
        $this->assertEquals('warning', $this->service->determineStatus(0.95, 0.95));
    }

    // =========================================================================
    // Calculate (all-in-one) Tests
    // =========================================================================

    /** @test */
    public function it_calculates_all_kpi_at_once_for_tol_semarang(): void
    {
        $result = $this->service->calculate(780, 910, 24, 28);

        $this->assertEqualsWithDelta(0.9, $result['cpi'], 0.0001);
        $this->assertEqualsWithDelta(0.9, $result['spi'], 0.0001);
        $this->assertEquals('critical', $result['status']);
    }

    /** @test */
    public function it_calculates_all_kpi_at_once_for_gedung_bumn(): void
    {
        $result = $this->service->calculate(270, 255, 14, 14);

        $this->assertGreaterThan(1, $result['cpi']);
        $this->assertEquals(1.0, $result['spi']);
        $this->assertEquals('good', $result['status']);
    }

    /** @test */
    public function it_calculates_all_kpi_at_once_for_bendungan_citarum(): void
    {
        $result = $this->service->calculate(580, 600, 30, 29);

        $this->assertEqualsWithDelta(1.0, $result['cpi'], 0.0001);
        $this->assertGreaterThan(1.0, $result['spi']);
        $this->assertEquals('warning', $result['status']);
    }

    /** @test */
    public function it_returns_array_with_correct_keys(): void
    {
        $result = $this->service->calculate(500, 500, 12, 12);

        $this->assertArrayHasKey('cpi', $result);
        $this->assertArrayHasKey('spi', $result);
        $this->assertArrayHasKey('status', $result);
    }
}