<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
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
        return [
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'make' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'type' => ['required', 'string', 'in:truck,van,trailer,pickup'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::enum(VehicleStatus::class)],
            'fitness_expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
