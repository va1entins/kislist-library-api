<?php

declare(strict_types=1);

namespace App\ValueObject;

// Reprezentuje 6-cyfrowy numer karty bibliotecznej wypożyczającego
final readonly class CardNumber
{
    public int $value;

    public function __construct(int $value)
    {
        if ($value < 100000 || $value > 999999) {
            throw new \InvalidArgumentException(
                sprintf('Numer karty bibliotecznej musi być liczbą 6-cyfrową, otrzymano: %d', $value)
            );
        }

        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
