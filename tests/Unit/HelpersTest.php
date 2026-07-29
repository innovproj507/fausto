<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testStrSlugLowercasesAndDashesNonAlphanumerics(): void
    {
        $this->assertSame('llave-de-bola-3-4', str_slug('Llave de Bola 3/4"'));
    }

    public function testStrSlugCollapsesRepeatedDashes(): void
    {
        $this->assertSame('a-b', str_slug('a---b'));
    }

    public function testStrSlugTrimsLeadingAndTrailingDashes(): void
    {
        $this->assertSame('product', str_slug('  Product!!  '));
    }

    public function testSanitizeEscapesHtml(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
            sanitize('<script>alert("xss")</script>')
        );
    }

    public function testMoneyFormatUsesCurrencySymbol(): void
    {
        $this->assertSame('$1,234.50', money_format(1234.5, 'USD'));
        $this->assertSame('€10.00', money_format(10, 'EUR'));
    }

    public function testMoneyFormatDefaultsToDollarSignForUnknownCurrency(): void
    {
        $this->assertSame('$5.00', money_format(5, 'XYZ'));
    }

    public function testTruncateLeavesShortStringsUntouched(): void
    {
        $this->assertSame('short', truncate('short', 10));
    }

    public function testTruncateCutsLongStringsAndAppendsEllipsis(): void
    {
        $this->assertSame('1234567890...', truncate('12345678901234', 10));
    }

    public function testEnvParsesBooleanAndNullStringsFromDotenvConvention(): void
    {
        putenv('TEST_BOOL_TRUE=true');
        putenv('TEST_BOOL_FALSE=false');
        putenv('TEST_NULL=null');
        putenv('TEST_EMPTY=empty');

        $this->assertTrue(env('TEST_BOOL_TRUE'));
        $this->assertFalse(env('TEST_BOOL_FALSE'));
        $this->assertNull(env('TEST_NULL'));
        $this->assertSame('', env('TEST_EMPTY'));

        putenv('TEST_BOOL_TRUE');
        putenv('TEST_BOOL_FALSE');
        putenv('TEST_NULL');
        putenv('TEST_EMPTY');
    }

    public function testEnvReturnsDefaultWhenUnset(): void
    {
        $this->assertSame('fallback', env('DEFINITELY_NOT_SET_ANYWHERE', 'fallback'));
    }
}
