<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_hours' => ['required', 'array'],
            'business_hours.*' => ['nullable', 'array'],
            'business_hours.*.open' => ['required_with:business_hours.*.close', 'date_format:H:i'],
            'business_hours.*.close' => ['required_with:business_hours.*.open', 'date_format:H:i'],
            'capacity' => ['required', 'integer', 'min:1'],
            'slot_interval_minutes' => ['required', 'integer', 'min:5'],
            'advance_booking_days' => ['required', 'integer', 'min:1'],
            'min_notice_hours' => ['required', 'integer', 'min:0'],
        ];
    }
}
