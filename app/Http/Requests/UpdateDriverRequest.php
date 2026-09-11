<?php

namespace App\Http\Requests;

use App\Enums\DriverStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
        $driverId = $this->route('driver')?->id ?? $this->route('driver');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('drivers', 'phone')->ignore($driverId)],
            'cnic' => ['sometimes', 'string', 'max:15', Rule::unique('drivers', 'cnic')->ignore($driverId)],
            'license_number' => ['sometimes', 'string', 'max:30', Rule::unique('drivers', 'license_number')->ignore($driverId)],
            'license_expires_at' => ['sometimes', 'date'],
            'license_document' => ['nullable', 'file', 'mimes:jpg,png,jpeg,pdf', 'max:5120'],
            'status' => ['sometimes', Rule::enum(DriverStatus::class)],
        ];
    }
}
