<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreateFormResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        // $this は CreateFormOutput を想定
        return [
            'form_id' => $this->formId,
            'public_token' => $this->publicToken,
            'organizer_token' => $this->organizerToken,
            'public_url' => $this->publicUrl,
            'manage_url' => $this->manageUrl,
        ];
    }
}
