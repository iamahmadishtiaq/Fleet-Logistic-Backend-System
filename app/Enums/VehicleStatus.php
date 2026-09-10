<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case AVAILABLE = 'available';
    case ON_TRIP = 'on_trip';
    case IN_MAINTENANCE = 'in_maintenance';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}