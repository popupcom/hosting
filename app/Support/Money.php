<?php

namespace App\Support;

final class Money
{
    public static function euro(float|int|string|null $amount): string
    {
        $n = is_numeric($amount) ? (float) $amount : 0.0;

        return number_format($n, 2, ',', '.').' €';
    }
}
