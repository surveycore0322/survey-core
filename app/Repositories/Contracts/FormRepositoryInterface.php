<?php

namespace App\Repositories\Contracts;

interface FormRepositoryInterface
{
    public function createForm(int $organizerId, string $title, string $type, ?string $eventDate): int;

    /** @param array<int, array{label:string,type:string,required:bool,sort_order:int,options:?array,purpose?:?string}> $rows */
    public function createQuestions(int $formId, array $rows): void;

    /** SubmitSnapshot 用：最小 */
    /** @return array<int, array{id:int,type:string,required:bool}> */
    public function getQuestionIndexByFormId(int $formId): array;

    /** GetFormDetail 用：詳細 */
    /**
     * @return array{
     *   id:int, organizer_id:int, title:string, type:string, event_date:?string
     * }|null
     */
    public function findFormByPublicToken(string $publicToken): ?array;

    /**
     * @return array<int, array{
     *   id:int,label:string,type:string,purpose:?string,options:?array,required:bool,sort_order:int
     * }>
     */
    public function findQuestionsByFormId(int $formId): array;

    /** Attend集計用：判定質問IDを決める（purpose優先） */
    public function findAttendIntentQuestionId(int $formId): ?int;

    /** @return array{id:int,organizer_id:int,title:string,type:string,event_date:?string}|null */
    public function findFormById(int $formId): ?array;
}
