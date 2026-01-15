<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\SubmitSnapshotRequest;
use App\Http\Resources\CreateFormResponseResource;
use App\Http\Resources\SubmitSnapshotResponseResource;
use App\Http\Resources\FormResultResource;
use App\UseCases\CreateFormUseCase;
use App\UseCases\GetFormDetailUseCase;
use App\UseCases\SubmitSnapshotUseCase;
use App\UseCases\GetFormResultUseCase;
use App\UseCases\DTO\CreateFormInput;
use App\UseCases\DTO\CreateQuestionInput;
use App\UseCases\DTO\GetFormDetailInput;
use App\UseCases\DTO\SubmitSnapshotInput;
use App\UseCases\DTO\AnswerInput;
use App\UseCases\DTO\GetFormResultInput;
use App\UseCases\Exceptions\UseCaseException;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FormResource;

final class FormController extends Controller
{
    public function __construct(
        private readonly CreateFormUseCase $createForm,
        private readonly GetFormDetailUseCase $getFormDetail,
        private readonly SubmitSnapshotUseCase $submitSnapshot,
        private readonly GetFormResultUseCase $getFormResult,
    ) {}

    /**
     * POST /api/v1/forms
     */
    public function create(CreateFormRequest $request): JsonResponse
    {
        try {
            $qs = null;
            if (is_array($request->input('questions'))) {
                $qs = array_map(function (array $q, int $i) {
                    return new CreateQuestionInput(
                        label: $q['label'],
                        type: $q['type'],
                        required: (bool)($q['required'] ?? false),
                        sortOrder: (int)($q['sort_order'] ?? $i),
                        options: $q['options'] ?? null,
                    );
                }, $request->input('questions'), array_keys($request->input('questions')));
            }

            $dto = new CreateFormInput(
                organizerNickname: $request->string('organizer_nickname')->toString(),
                title: $request->string('title')->toString(),
                type: $request->string('type')->toString(),
                eventDate: $request->input('event_date'),
                questions: $qs,
            );

            $out = $this->createForm->execute($dto);

            return (new CreateFormResponseResource($out))
                ->response()
                ->setStatusCode(201);

        } catch (UseCaseException $e) {
            return $this->useCaseError($e);
        }
    }

    /**
     * GET /api/v1/forms/{public_token}
     */
		public function show(string $public_token): JsonResponse
		{
		    try {
		        $out = $this->getFormDetail->execute(new GetFormDetailInput($public_token));

		        return response()->json([
		            'form' => new FormResource($out->form),
		        ]);

		    } catch (UseCaseException $e) {
		        return $this->useCaseError($e, 404);
		    }
		}

    /**
     * POST /api/v1/forms/{public_token}/snapshots
     */
    public function submitSnapshot(SubmitSnapshotRequest $request, string $public_token): JsonResponse
    {
        try {
            $answers = array_map(function (array $a) {
                return new AnswerInput(
                    questionId: (int)$a['question_id'],
                    value: $a['value'] ?? null
                );
            }, $request->input('answers'));

            $dto = new SubmitSnapshotInput(
                publicToken: $public_token,
                nickname: $request->string('nickname')->toString(),
                participantToken: $request->input('participant_token'),
                answers: $answers,
            );

            $out = $this->submitSnapshot->execute($dto);

            return (new SubmitSnapshotResponseResource($out))
                ->response()
                ->setStatusCode(201);

        } catch (UseCaseException $e) {
            return $this->useCaseError($e);
        }
    }

    /**
     * GET /api/v1/forms/{public_token}/results
     */
    public function results(string $public_token): JsonResponse
    {
        try {
            $out = $this->getFormResult->execute(new GetFormResultInput(publicToken: $public_token));

            return (new FormResultResource($out))
                ->response()
                ->setStatusCode(200);

        } catch (UseCaseException $e) {
            return $this->useCaseError($e, 404);
        }
    }

    private function useCaseError(UseCaseException $e, int $fallbackStatus = 400): JsonResponse
    {
        $status = $e->getCode();
        if (!is_int($status) || $status < 400 || $status > 599) {
            $status = $fallbackStatus;
        }

        return response()->json([
            'error' => [
                'code' => $e->codeKey,
                'message' => $e->getMessage(),
                'detail' => $e->detail,
            ],
        ], $status);
    }
}
