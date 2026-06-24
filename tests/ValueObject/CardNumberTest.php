<?php
declare(strict_types=1);

namespace App\Tests\ValueObject;

use App\ValueObject\CardNumber;
use PHPUnit\Framework\TestCase;

final class CardNumberTest extends TestCase
{
    // Sprawdza akceptację poprawnych 6-cyfrowych numerów (wartości granic i typowa)
    public function testCreatesValidCardNumber(): void
    {
        $this->assertSame(100000, (new CardNumber(100000))->toInt());
        $this->assertSame(999999, (new CardNumber(999999))->toInt());
        $this->assertSame(123456, (new CardNumber(123456))->toInt());
    }

    // Sprawdza odrzucenie liczby zbyt krótkiej (5 cyfr)
    public function testRejectsNumberBelowSixDigits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CardNumber(99999);
    }

    // Sprawdza odrzucenie liczby zbyt długiej (7 cyfr)
    public function testRejectsNumberAboveSixDigits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CardNumber(1000000);
    }

    // Sprawdza odrzucenie liczby ujemnej
    public function testRejectsNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CardNumber(-123456);
    }

    // Sprawdza metodę equals() dla dwóch instancji z tą samą wartością
    public function testEqualsReturnsTrueForSameValue(): void
    {
        $a = new CardNumber(123456);
        $b = new CardNumber(123456);

        $this->assertTrue($a->equals($b));
    }

    // Sprawdza metodę equals() dla różnych wartości
    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = new CardNumber(123456);
        $b = new CardNumber(654321);

        $this->assertFalse($a->equals($b));
    }
}
