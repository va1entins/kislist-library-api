<?php

declare(strict_types=1);

namespace App\Dto\Request;

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
            throw new \InvalidArgumentException('Pola "id", "title" oraz "author" są wymagane.');
        }

        if (!is_int($data['id']) && !ctype_digit((string) $data['id'])) {
            throw new \InvalidArgumentException('Pole "id" musi być liczbą całkowitą.');
        }

        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            author: (string) $data['author'],
        );
    }
}
