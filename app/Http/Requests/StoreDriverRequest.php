<?php

namespace App\Http\Requests;

use App\Enums\DriverStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:drivers,phone'],
            'cnic' => ['required', 'string', 'max:15', 'uniuq:drivers,cnic'],
            'license_number' => ['required', 'string', 'max;30', 'unique:drivers,license'],
            'license_expires_at' => ['required', 'date', 'after:today'],
            'license_document' => ['nullable', 'file', 'mimes:jpg,png,jpeg,pdf', 'max:5120'],
            'status' => ['nullable', Rule::enum(DriverStatus::class)],
        ];
    }
}
