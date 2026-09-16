<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DriverStatus;
use App\Enums\VehicleStatus;
use App\Models\Driver;
use App\Models\Vehicle;

class StoreTripRequest extends FormRequest
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
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'origin' => ['required', 'string', 'max:30'],
            'destination' => ['required', 'string', 'max:100'],
            'cargo_details' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator){
            if($this->vehicle_id){
                $vehicle = Vehicle::find($this->vehicle_id);
                if($vehicle && $vehicle->status !== VehicleStatus::AVAILABLE){
                    $validator->errors()->add('vehicle_id', 'This Vehicle is not currently available for dispatch');
                }
            }

            if($this->driver_id){
                $driver = Driver::find($this->driver_id);
                if($driver && $driver->status !== DriverStatus::AVAILABLE){
                    $validator->errors()->add('driver_id', 'This Driver is not currently available for assignment');
                }
            }
        });
    }
}
