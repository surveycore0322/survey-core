<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

final class ValueCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): mixed
    {
        // DBから来る$valueは通常「JSON文字列」
        if ($value === null) {
            return null;
        }

        // すでに配列等で来るケースはそのまま
        if (is_array($value) || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            // 文字列が「JSONじゃない文字列」だった場合は、そのまま文字列として返す
            return $value;
        }

        // object等は配列に変換
        if (is_object($value)) {
            return json_decode(json_encode($value, JSON_UNESCAPED_UNICODE), true);
        }

        return $value;
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        // MySQL JSONカラムは、ドライバによって「素のbool/array」を渡すと不安定になり得るので
        // “常に JSON文字列” にして渡すのが一番安全です。
        // JSON_OBJECT / JSON_ARRAY も含め確実に通します。
        if ($value === null) {
            return [$key => null];
        }

        if ($this->isJsonSerializableScalar($value) || is_array($value)) {
            return [$key => json_encode($value, JSON_UNESCAPED_UNICODE)];
        }

        if (is_object($value)) {
            return [$key => json_encode($value, JSON_UNESCAPED_UNICODE)];
        }

        throw new InvalidArgumentException("Unsupported value type for {$key}");
    }

    private function isJsonSerializableScalar(mixed $v): bool
    {
        return is_bool($v) || is_int($v) || is_float($v) || is_string($v);
    }
}
