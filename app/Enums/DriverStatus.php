<?php

namespace App\Enums;

enum DriverStatus: string
{
    case AVAILABLE = 'available';
    case ON_TRIP = 'on_trip';
    case SUSPEND = 'suspend';

    public static function values(): array
    {
        return array_column(self::cases(), 'values');
    }
}