<?php

namespace Tests\Unit;

use App\Support\MenuFormatter;
use PHPUnit\Framework\TestCase;

class MenuFormatterTest extends TestCase
{
    public function test_it_matches_the_original_persian_number_format(): void
    {
        self::assertSame('۱٬۲۳۴٬۵۶۷', MenuFormatter::toman(1234567));
        self::assertSame('۰', MenuFormatter::toman(0));
    }
}
