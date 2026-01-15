<?php

namespace App\UseCases;

use App\Domain\Token\TokenType;
use App\Repositories\Contracts\FormRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\UseCases\Assemblers\FormAssembler;
use App\UseCases\Assemblers\TokenAssembler;
use App\UseCases\DTO\GetFormDetailInput;
use App\UseCases\DTO\GetFormDetailOutput;
use App\UseCases\DTO\FormDTO;
use App\UseCases\DTO\QuestionDTO;
use App\UseCases\Exceptions\UseCaseException;

final class GetFormDetailUseCase
{
    public function __construct(
        private TokenRepositoryInterface $tokenRepo,
        private FormRepositoryInterface $formRepo,
        private TokenAssembler $tokenAssembler,
        private FormAssembler $formAssembler,
    ) {}

    public function execute(GetFormDetailInput $in): GetFormDetailOutput
    {
        // 1) public_token -> forms.id
        $hash = $this->tokenAssembler->hashRaw($in->publicToken);
        $tokenable = $this->tokenRepo->findTokenableByHash($hash, TokenType::FormPublic->value);

        if (!$tokenable || $tokenable['tokenable_type'] !== 'forms') {
            throw new UseCaseException('FORM_NOT_FOUND', 'Form not found.', [], 404);
        }
        $formId = (int)$tokenable['tokenable_id'];

        // 2) form + questions を取得
        $formRow = $this->formRepo->findFormById($formId);
        if (!$formRow) {
            throw new UseCaseException('FORM_NOT_FOUND', 'Form not found.', [], 404);
        }

        $questionRows = $this->formRepo->findQuestionsByFormId($formId);

        // 3) Domain化（ここが統一ポイント）
        $form = $this->formAssembler->fromDetailRows($formRow, $questionRows);

        // 4) Output DTO へ（Controller/Resourceが扱いやすい形）
        $formDto = new FormDTO(
            id: $form->id,
            title: $form->title,
            type: $form->type->value,
            eventDate: $form->eventDate,
            questions: array_map(fn($q) => new QuestionDTO(
                id: $q->id,
                label: $q->label,
                type: $q->type,
                options: $q->options,
                required: $q->required,
                sortOrder: $q->sortOrder,
            ), $form->questions),
        );

        return new GetFormDetailOutput($formDto);
    }
}
