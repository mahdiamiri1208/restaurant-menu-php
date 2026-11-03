<?php

namespace App\Support;

use NumberFormatter;
use RuntimeException;

final class MenuFormatter
{
    public static function toman(int $value): string
    {
        $formatter = new NumberFormatter('fa_IR', NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new RuntimeException('Unable to format the menu price.');
        }

        return $formatted;
    }
}
