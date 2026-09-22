<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'fuel_rate_per_liter',
        'toll_tax',
        'misc_expenses',
        'fuel_cost',
        'total_cost',
        'status',
        'cancellation_reason',
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

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })

            ->when($filters['vehicle_id'] ?? null, function ($q, $vehicleId) {
                $q->where('vehicle_id', $vehicleId);
            })

            ->when($filters['driver_id'] ?? null, function ($q, $driverId) {
                $q->where('driver_id', $driverId);
            })

            ->when($filters['from_date'] ?? null, function ($q, $fromDate){
                $q->where('started_at', '>=', $fromDate);
            })

            ->when($filters['to_date'] ?? null, function ($q, $toDate){
                $q->where('started_at', '<=', $toDate);          
            })

            ->when($filters['search'] ?? null, function ($q, $search){
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('trip_number', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            });
    }

    public function locations(): HasMany
    {
        return $this->hasMany(TripLocation::class)->latest('recorded_at');
    }

    public function latestLocation(): HasOne
    {
        return $this->hasOne(TripLocation::class)->latestOfMany('recorded_at');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->latest();
    }

}
