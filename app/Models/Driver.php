<?php

namespace App\Models;

use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'cnic',
        'license_number',
        'license_expires_at',
        'license_document',
        'status',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => DriverStatus::class,
            'lisence_expires_at' => 'date',
        ];
    }
}
