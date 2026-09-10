<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\VehicleStatus;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id ?? $this->route('vehicle');


        return [
            'plate_number' => ['sometimes', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicleId)],
            'make' => ['sometimes', 'string', 'max:50'],
            'model' => ['sometimes', 'string', 'max:50'],
            'year' => ['sometimes', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'type' => ['sometimes', 'string', 'in:truck,van,trailer,pickup'],
            'odometer' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::enum(VehicleStatus::class)],
            'fitness_expires_at' => ['nullable', 'date'],
        ];
    }
}
