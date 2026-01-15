<?php

namespace App\UseCases;

use App\Domain\Token\TokenType;
use App\Repositories\Contracts\FormRepositoryInterface;
use App\Repositories\Contracts\OrganizerRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\UseCases\Assemblers\TokenAssembler;
use App\UseCases\DTO\CreateFormInput;
use App\UseCases\DTO\CreateFormOutput;

final class CreateFormUseCase
{
    public function __construct(
        private OrganizerRepositoryInterface $organizerRepo,
        private FormRepositoryInterface $formRepo,
        private TokenRepositoryInterface $tokenRepo,
        private TokenAssembler $tokenAssembler,
    ) {}

    public function execute(CreateFormInput $in): CreateFormOutput
    {
        $organizerId = $this->organizerRepo->create($in->organizerNickname);

        $formId = $this->formRepo->createForm($organizerId, $in->title, $in->type, $in->eventDate);

        // questions 同梱がある場合
        if (is_array($in->questions) && count($in->questions) > 0) {
            $rows = [];
            foreach ($in->questions as $q) {
                $rows[] = [
                    'label' => $q->label,
                    'type' => $q->type,
                    'required' => $q->required,
                    'sort_order' => $q->sortOrder,
                    'options' => $q->options,
                ];
            }
            $this->formRepo->createQuestions($formId, $rows);
        }

        // Domain TokenEntity を発行（raw/hashを保持）
        $publicToken = $this->tokenAssembler->issue(TokenType::FormPublic, 'forms', $formId);
        $organizerToken = $this->tokenAssembler->issue(TokenType::Organizer, 'organizers', $organizerId);

        // 永続化はhashだけ
        $this->tokenRepo->storeToken($publicToken->tokenHash, $publicToken->type->value, $publicToken->tokenableType, $publicToken->tokenableId, $publicToken->expiresAt);
        $this->tokenRepo->storeToken($organizerToken->tokenHash, $organizerToken->type->value, $organizerToken->tokenableType, $organizerToken->tokenableId, $organizerToken->expiresAt);

        $publicUrl = url("/attend/{$publicToken->rawToken}");
        $manageUrl = url("/attend/manage/{$organizerToken->rawToken}");

        return new CreateFormOutput(
            formId: $formId,
            publicToken: $publicToken->rawToken,
            organizerToken: $organizerToken->rawToken,
            publicUrl: $publicUrl,
            manageUrl: $manageUrl,
        );
    }
}
