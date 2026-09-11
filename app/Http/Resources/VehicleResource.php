<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plate_number' => $this->plate_number,
            'vehicle_name' => "{$this->make} {$this->model} ({$this->year})",
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'type' => $this->type,
            'odometer_km' => $this->odometer,
            'status' => [
                'value' => $this->status->value,
                'is_available' => $this->status->value === 'available',
            ],
            'fitness_expires_at' => $this->fitness_expires_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
