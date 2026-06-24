<?php

declare(strict_types=1);

namespace App\Dto\Request;

use App\Exception\InvalidRequestException;

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
            throw new InvalidRequestException('Pole "status" jest wymagane.');
        }

        $status = $data['status'];
        if (!in_array($status, ['borrowed', 'available'], true)) {
            throw new InvalidRequestException('Pole "status" musi mieć wartość "borrowed" lub "available".');
        }

        $isBorrowed = 'borrowed' === $status;

        // borrowerCardNumber wymagany tylko przy wypożyczeniu, przy zwrocie pole jest ignorowane
        if ($isBorrowed && !isset($data['borrowerCardNumber'])) {
            throw new InvalidRequestException('Pole "borrowerCardNumber" jest wymagane przy statusie "borrowed".');
        }

        $cardNumber = $isBorrowed ? (int) $data['borrowerCardNumber'] : null;

        return new self(
            isBorrowed: $isBorrowed,
            borrowerCardNumber: $cardNumber,
        );
    }
}
