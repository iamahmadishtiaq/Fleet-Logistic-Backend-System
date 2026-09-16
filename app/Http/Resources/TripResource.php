<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $statusValue = $this->status instanceof \BackedEnum
            ? $this->status->value
            : ($this->status ?? 'scheduled');

        return [
            'id' => $this->id,
            'trip_number' => $this->trip_number,
            'route' => "{$this->origin} -> {$this->destination}",
            'origin' => $this->origin,
            'destination' => $this->destination,
            'cargo_details' => $this->cargo_details,
            'odometer' => [
                'start_km' => $this->start_odometer,
                'end_km' => $this->end_odometer,
                'distance_covered' => $this->end_odometer ? ($this->end_odometer - $this->start_odometer): null,
            ],
            'status' => $statusValue,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'driver' => new DriverResource($this->whenLoaded('driver')),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
