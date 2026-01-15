<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormResultResource extends JsonResource
{
    public function toArray($request): array
    {
        // $this は GetFormResultOutput を想定
        return [
            'form_id' => $this->formId,
            'title' => $this->title,
            'event_date' => $this->eventDate,
            'results' => [
                'attend' => $this->attend,
                'absent' => $this->absent,
            ],
        ];
    }
}
