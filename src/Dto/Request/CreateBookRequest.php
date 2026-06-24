<?php

declare(strict_types=1);

namespace App\Dto\Request;

use App\Exception\InvalidRequestException;

final readonly class CreateBookRequest
{
    public function __construct(
        public int $id,
        public string $title,
        public string $author,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['id'], $data['title'], $data['author'])) {
            throw new InvalidRequestException('Pola "id", "title" oraz "author" są wymagane.');
        }

        // Akceptujemy int lub string cyfrowy — JSON może przekazać id jako liczbę lub string
        if (!is_int($data['id']) && !ctype_digit((string) $data['id'])) {
            throw new InvalidRequestException('Pole "id" musi być liczbą całkowitą.');
        }

        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            author: (string) $data['author'],
        );
    }
}
