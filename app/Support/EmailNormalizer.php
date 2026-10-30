<?php

namespace App\Support;

use Illuminate\Support\Str;

class EmailNormalizer
{
    public static function normalize(string $email): string
    {
        return Str::lower(preg_replace('/\s+/u', '', $email) ?? $email);
    }
}
