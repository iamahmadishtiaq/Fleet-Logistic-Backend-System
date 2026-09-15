<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expiresAt = $this->license_expires_at instanceof Carbon ? $this->license_expires_at : ($this->license_expires_at ? Carbon::parse($this->license_expires_at) : null);

        // Status string ya enum nikaalna
        $statusValue = $this->status instanceof \BackedEnum ? $this->status->value : ($this->status ?? 'available');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'cnic' => $this->cnic,
            'license_number' => $this->license_number,
            'license_expires_at' => $this->license_expires_at?->format('Y-m-d'),
            'is_license_valid' => $this->license_expires_at?->isFuture() ?? false,
            'document_url' => $this->document_url,
            'status' => [
                'value' => $this->status->value,
                'is_available' => $this->status->value === 'available',
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
