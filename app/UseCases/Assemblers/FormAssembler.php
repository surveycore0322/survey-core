<?php

namespace App\UseCases\Assemblers;

use App\Domain\Form\FormEntity;
use App\Domain\Form\FormType;
use App\Domain\Form\QuestionEntity;

final readonly class FormAssembler
{
    /**
     * @param array{id:int,organizer_id:int,title:string,type:string,event_date:?string} $formRow
     * @param array<int, array{id:int,label:string,type:string,purpose:?string,options:?array,required:bool,sort_order:int}> $questionRows
     */
    public function fromDetailRows(array $formRow, array $questionRows): FormEntity
    {
        $formId = (int)$formRow['id'];

        $questions = [];
        foreach ($questionRows as $q) {
            $questions[] = new QuestionEntity(
                id: (int)$q['id'],
                formId: $formId,
                label: (string)$q['label'],
                type: (string)$q['type'],
                options: $q['options'] ?? null,
                required: (bool)$q['required'],
                sortOrder: (int)$q['sort_order'],
            );
        }

        return new FormEntity(
            id: $formId,
            organizerId: (int)$formRow['organizer_id'],
            title: (string)$formRow['title'],
            type: FormType::fromString((string)$formRow['type']),
            eventDate: $formRow['event_date'],
            questions: $questions,
        );
    }
}
