<?php

namespace Tests\Unit;

use App\Support\MenuPrice;
use PHPUnit\Framework\TestCase;

class MenuPriceTest extends TestCase
{
    public function test_it_calculates_discounted_prices_with_javascript_rounding(): void
    {
        self::assertSame(327250, MenuPrice::final(385000, 15));
        self::assertSame(51, MenuPrice::final(101, 50));
    }

    public function test_null_and_zero_discounts_leave_the_price_unchanged(): void
    {
        self::assertSame(495000, MenuPrice::final(495000, null));
        self::assertSame(495000, MenuPrice::final(495000, 0));
    }
}
