<?php
declare(strict_types=1);

namespace App\Tests\ValueObject;

use App\ValueObject\SerialNumber;
use PHPUnit\Framework\TestCase;

final class SerialNumberTest extends TestCase
{
    // Sprawdza akceptację poprawnych 6-cyfrowych numerów (wartości granic i typowa)
    public function testCreatesValidSerialNumber(): void
    {
        $this->assertSame(100000, (new SerialNumber(100000))->toInt());
        $this->assertSame(999999, (new SerialNumber(999999))->toInt());
        $this->assertSame(123456, (new SerialNumber(123456))->toInt());
    }

    // Sprawdza odrzucenie liczby zbyt krótkiej (5 cyfr)
    public function testRejectsNumberBelowSixDigits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SerialNumber(99999);
    }

    // Sprawdza odrzucenie liczby zbyt długiej (7 cyfr)
    public function testRejectsNumberAboveSixDigits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SerialNumber(1000000);
    }

    // Sprawdza odrzucenie liczby ujemnej
    public function testRejectsNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SerialNumber(-123456);
    }

    // Sprawdza metodę equals() dla dwóch instancji z tą samą wartością
    public function testEqualsReturnsTrueForSameValue(): void
    {
        $a = new SerialNumber(123456);
        $b = new SerialNumber(123456);

        $this->assertTrue($a->equals($b));
    }

    // Sprawdza metodę equals() dla różnych wartości
    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = new SerialNumber(123456);
        $b = new SerialNumber(654321);

        $this->assertFalse($a->equals($b));
    }
}
