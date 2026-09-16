<?php

namespace App\Enums;

enum TripStatus: string
{
    case SCHEDULED = 'scheduled';
    case IN_TRANSIT = 'in_transit';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function values():array
    {
        return array_column(self::cases(), 'value');
    }
}
