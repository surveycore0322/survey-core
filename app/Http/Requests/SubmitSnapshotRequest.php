<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MVP: 認証なし
    }

    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'max:100'],
            'participant_token' => ['nullable', 'string', 'max:255'],

            // DTOに合わせて配列で受ける（A案：question_id重複はUseCaseで検知）
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'min:1'],
            'answers.*.value' => ['present'], // nullも許容するため present（requiredだとnull落ちる）
        ];
    }
}
