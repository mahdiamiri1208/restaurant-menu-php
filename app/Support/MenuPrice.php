<?php

namespace App\Support;

final class MenuPrice
{
    public static function final(int $price, ?int $discountPercent): int
    {
        if ($discountPercent === null || $discountPercent === 0) {
            return $price;
        }

        return (int) round($price * (100 - $discountPercent) / 100, 0, PHP_ROUND_HALF_UP);
    }
}
