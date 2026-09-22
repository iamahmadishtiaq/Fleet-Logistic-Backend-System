<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'reported_by',
        'type',
        'severity',
        'description',
        'latitude',
        'longitude',
        'estimated_delay_hours',
        'status'
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'estimated_delay_hours' => 'float',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
