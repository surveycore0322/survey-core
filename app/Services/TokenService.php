<?php
namespace App\Services;

use Illuminate\Support\Str;

final class TokenService
{
    public function generateRawToken(string $prefix): string
    {
        // 例：短めのURLトークン（好みで変更）
        return $prefix . '_' . Str::random(16);
    }

    public function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
