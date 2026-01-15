<?php

namespace App\Services\Scalarizer;

interface ScalarizerInterface
{
    /**
     * @param string $questionType 例: "boolean", "single_choice"...
     * @param mixed  $value        Answer.value（JsonValue）
     */
    public function scalarize(string $questionType, mixed $value): ?string;
}
