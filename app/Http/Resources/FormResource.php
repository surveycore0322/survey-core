<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 *@property object $resource  // FormDTO を想定
 */
class FormResource extends JsonResource
{
    public function toArray($request):array
    {
        // $this は GetFormDetailOutput->form（FormDTO）を想定
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'event_date' => $this->eventDate,
            'questions' => array_map(static fn($q) => [
                'id' => $q->id,
                'label' => $q->label,
                'type' => $q->type,
                'options' => $q->options,
                'required' => $q->required,
                'sort_order' => $q->sortOrder,
             ], $this->questions),
        ];
    }
}
