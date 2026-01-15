<?php

namespace App\Repositories\Eloquent;

use App\Models\Form;
use App\Models\Question;
use App\Repositories\Contracts\FormRepositoryInterface;

final class FormRepository implements FormRepositoryInterface
{
    public function createForm(int $organizerId, string $title, string $type, ?string $eventDate): int
    {
        $form = Form::create([
            'organizer_id' => $organizerId,
            'title' => $title,
            'type' => $type,
            'event_date' => $eventDate,
        ]);
        return (int)$form->id;
    }

    public function createQuestions(int $formId, array $rows): void
    {
        foreach ($rows as $r) {
            Question::create([
                'form_id' => $formId,
                'label' => $r['label'],
                'type' => $r['type'],
                'purpose' => $r['purpose'] ?? null,
                'required' => (bool)$r['required'],
                'sort_order' => (int)$r['sort_order'],
                'options' => $r['options'] ?? null,
            ]);
        }
    }

    public function getQuestionIndexByFormId(int $formId): array
    {
        return Question::query()
            ->where('form_id', $formId)
            ->orderBy('sort_order')
            ->get(['id','type','required'])
            ->map(fn($q) => [
                'id' => (int)$q->id,
                'type' => (string)$q->type,
                'required' => (bool)$q->required,
            ])->all();
    }

    public function findFormByPublicToken(string $publicToken): ?array
    {
        // publicToken → form_id は tokens 経由が原則（既にSubmitSnapshotで採用）
        // ここはUseCase側で token 解決して formId を得る形に寄せるのが統一的。
        // ただ「GET /forms/{public_token}」はpublic_tokenで来るので、UseCaseで tokenRepo を使う想定にします。
        // そのため、このメソッドは「formIdが分かった後に呼ぶ」用途に変えると綺麗です。
        return null;
    }

    public function findQuestionsByFormId(int $formId): array
    {
        return Question::query()
            ->where('form_id', $formId)
            ->orderBy('sort_order')
            ->get(['id','label','type','purpose','options','required','sort_order'])
            ->map(fn($q) => [
                'id' => (int)$q->id,
                'label' => (string)$q->label,
                'type' => (string)$q->type,
                'purpose' => $q->purpose !== null ? (string)$q->purpose : null,
                'options' => $q->options,
                'required' => (bool)$q->required,
                'sort_order' => (int)$q->sort_order,
            ])->all();
    }

    public function findAttendIntentQuestionId(int $formId): ?int
    {
        $q = Question::query()
            ->where('form_id', $formId)
            ->where('purpose', 'attend_intent')
            ->orderBy('sort_order')
            ->first(['id']);

        if ($q) return (int)$q->id;

        // フォールバック：先頭boolean
        $q2 = Question::query()
            ->where('form_id', $formId)
            ->where('type', 'boolean')
            ->orderBy('sort_order')
            ->first(['id']);

        return $q2 ? (int)$q2->id : null;
    }

	public function findFormById(int $formId): ?array
    {
        $f = Form::query()->find($formId, ['id','organizer_id','title','type','event_date']);
        if (!$f) return null;

        return [
            'id' => (int)$f->id,
            'organizer_id' => (int)$f->organizer_id,
            'title' => (string)$f->title,
            'type' => (string)$f->type,
            'event_date' => $f->event_date?->toIso8601String(),
        ];
    }
}