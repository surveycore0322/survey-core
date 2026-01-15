<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MVP: 認証なし
    }

    public function rules(): array
    {
        return [
            'organizer_nickname' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'event_date' => ['nullable', 'date'],

            // questionsはMVPでは任意（固定質問にするならnull許可）
            'questions' => ['nullable', 'array'],
            'questions.*.label' => ['required_with:questions', 'string', 'max:255'],
            'questions.*.type' => ['required_with:questions', 'string', 'max:50'],
            'questions.*.required' => ['sometimes', 'boolean'],
            'questions.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['string', 'max:100'],
        ];
    }
}
