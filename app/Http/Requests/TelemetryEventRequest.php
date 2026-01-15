<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelemetryEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MVP: 認証なし
    }

    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', 'max:100'],

            'form_id' => ['nullable', 'integer', 'min:1'],
            'view_id' => ['nullable', 'string', 'max:64'], // UUID想定（厳密にuuidでもOK）
            'participant_token' => ['nullable', 'string', 'max:255'],

            'client_ts' => ['nullable', 'date'],
            'device_type' => ['nullable', 'string', 'max:50'],
            'locale' => ['nullable', 'string', 'max:20'],
            'user_agent' => ['nullable', 'string', 'max:255'],

            'properties' => ['nullable', 'array'],
        ];
    }
}
