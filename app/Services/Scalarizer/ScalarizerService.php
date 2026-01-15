<?php

namespace App\Services\Scalarizer;

use Carbon\CarbonImmutable;

final class ScalarizerService implements ScalarizerInterface
{
    public function scalarize(string $questionType, mixed $value): ?string
    {
        $type = strtolower($questionType);

        return match ($type) {
            'boolean' => $this->scalarizeBooleanAttend($value),
            'single_choice' => $this->scalarizeSingleChoice($value),
            'multi_choice' => $this->scalarizeMultiChoice($value),
            'number', 'rating' => $this->scalarizeNumber($value),
            'date' => $this->scalarizeDate($value),
            'text' => null,
            default => $this->scalarizeFallback($value),
        };
    }

    /**
     * Attend MVP: booleanは ATTEND/DECLINE に固定
     */
    private function scalarizeBooleanAttend(mixed $value): ?string
    {
        $b = $this->toBool($value);
        if ($b === null) return null;
        return $b ? 'ATTEND' : 'DECLINE';
    }

    private function scalarizeSingleChoice(mixed $value): ?string
    {
        if ($value === null) return null;
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
            return null;
        }
        return 'OPT:' . $this->normalizeScalar((string)$value);
    }

    private function scalarizeMultiChoice(mixed $value): ?string
    {
        if (!is_array($value)) return null;

        $parts = [];
        foreach ($value as $v) {
            if (is_string($v) || is_int($v) || is_float($v) || is_bool($v)) {
                $parts[] = $this->normalizeScalar((string)$v);
            }
        }
        if (count($parts) === 0) return null;

        sort($parts); // 順序ブレ排除（SQL集計の安定化）
        return 'OPTS:' . implode('|', $parts);
    }

    private function scalarizeNumber(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) return (string)$value;
        if (is_string($value) && is_numeric($value)) return (string)$value;
        return null;
    }

    private function scalarizeDate(mixed $value): ?string
    {
        if ($value === null) return null;

        // ISO文字列 "2026-02-01" or "2026-02-01T..." を想定
        if (is_string($value)) {
            try {
                return CarbonImmutable::parse($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function scalarizeFallback(mixed $value): ?string
    {
        // 最後の砦：スカラ値だけは文字列化して返す
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_int($value) || is_float($value)) return (string)$value;
        if (is_string($value)) return $this->normalizeScalar($value);
        return null;
    }

    private function toBool(mixed $value): ?bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1 ? true : ($value === 0 ? false : null);
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['1', 'true', 'yes', 'y', 'on'], true)) return true;
            if (in_array($v, ['0', 'false', 'no', 'n', 'off'], true)) return false;
        }
        return null;
    }

    private function normalizeScalar(string $s): string
    {
        // SQLで揺れないための最低限の正規化
        $s = trim($s);
        $s = mb_strtolower($s);
        // 空白や連続スペースを潰す
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return $s;
    }
}
