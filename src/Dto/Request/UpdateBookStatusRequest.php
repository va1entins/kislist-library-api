<?php

declare(strict_types=1);

namespace App\Dto\Request;

final readonly class UpdateBookStatusRequest
{
    public function __construct(
        public bool $isBorrowed,
        public ?int $borrowerCardNumber,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['status'])) {
            throw new \InvalidArgumentException('Pole "status" jest wymagane.');
        }

        $status = $data['status'];
        if (!in_array($status, ['borrowed', 'available'], true)) {
            throw new \InvalidArgumentException('Pole "status" musi mieć wartość "borrowed" lub "available".');
        }

        $isBorrowed = $status === 'borrowed';

        if ($isBorrowed && !isset($data['borrowerCardNumber'])) {
            throw new \InvalidArgumentException('Pole "borrowerCardNumber" jest wymagane przy statusie "borrowed".');
        }

        $cardNumber = $isBorrowed ? (int) $data['borrowerCardNumber'] : null;

        return new self(
            isBorrowed: $isBorrowed,
            borrowerCardNumber: $cardNumber,
        );
    }
}
