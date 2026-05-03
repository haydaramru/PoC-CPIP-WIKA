<?php

namespace App\Services;

use App\Enums\Division;

class DivisionResolver
{
    private const INFRASTRUCTURE_PREFIXES = [
        'JBT', 'DAM', 'TOL', 'JLN', 'BDR', 'RIL', 'RAW', 'PLB', 'IRG', 'PEL',
        'EPC', 'IND', 'INF', 'PWR', 'OIL', 'GAS', 'MIN',
    ];

    private const BUILDING_PREFIXES = [
        'GDG', 'RSU', 'RST', 'APT', 'MAL', 'HTL', 'KMP', 'OFC', 'RES',
    ];

    public static function fromCode(?string $projectCode): ?string
    {
        if (empty($projectCode)) return null;

        $upper = strtoupper(trim($projectCode));
        $prefix = preg_split('/[-_\s]/', $upper)[1] ?? substr($upper, 0, 3);

        if (in_array($prefix, self::INFRASTRUCTURE_PREFIXES, true)) {
            return Division::Infrastructure->value;
        }
        if (in_array($prefix, self::BUILDING_PREFIXES, true)) {
            return Division::Building->value;
        }

        // Fallback: scan first token
        foreach (self::INFRASTRUCTURE_PREFIXES as $p) {
            if (str_contains($upper, $p)) return Division::Infrastructure->value;
        }
        foreach (self::BUILDING_PREFIXES as $p) {
            if (str_contains($upper, $p)) return Division::Building->value;
        }

        return null;
    }
}
