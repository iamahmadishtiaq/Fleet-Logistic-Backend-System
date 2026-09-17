<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Testing\Fluent\Concerns\Has;
use Override;
use Ramsey\Uuid\Type\Integer;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'make',
        'model',
        'year',
        'type',
        'odometer',
        'last_service_odometer',
        'status',
        'fitness_expires_at',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => VehicleStatus::class,
            'fitness_expires_at' => 'date',
            'odometer' => 'integer',
            'year' => 'integer',
        ];
    }

    public function requiresMaintenance(int $intervalKm = 10000): bool
    {
        return ($this->odometer - $this->last_service_odometer) >= $intervalKm;
    }
}
