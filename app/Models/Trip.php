<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_number',
        'vehicle_id',
        'driver_id',
        'destination',
        'origin',
        'cargo_details',
        'start_odometer',
        'end_odometer',
        'status',
        'started_at',
        'completed_at',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'status' => TripStatus::class,
            'start_odometer' => 'integer',
            'end_odometer' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime'
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
