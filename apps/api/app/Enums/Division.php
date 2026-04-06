<?php

namespace App\Enums;

enum Division: string
{
    case Infrastructure = 'Infrastructure';
    case Building       = 'Building';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
