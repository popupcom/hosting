<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class SafeCustomCss implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (strlen($value) > 50_000) {
            $fail('Das Custom CSS darf höchstens 50.000 Zeichen umfassen.');

            return;
        }

        if (preg_match('/\b(expression\s*\(|<script|@import\b|javascript\s*:|data:text\/html|<\/style|url\s*\(\s*[\'"]?https?:)/i', $value) === 1) {
            $fail('Im Custom CSS sind Ausdrücke wie @import, expression(, externe url(http…), script-Tags oder javascript:-URLs nicht erlaubt.');
        }
    }
}
