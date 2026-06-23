<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Book;

final readonly class BookResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public string $author,
        public bool $isBorrowed,
        public ?string $borrowedAt,
        public ?int $borrowerCardNumber,
    ) {
    }

    public static function fromEntity(Book $book): self
    {
        return new self(
            id: $book->getId(),
            title: $book->getTitle(),
            author: $book->getAuthor(),
            isBorrowed: $book->isBorrowed(),
            borrowedAt: $book->getBorrowedAt()?->format(\DateTimeInterface::ATOM),
            borrowerCardNumber: $book->getBorrowerCardNumber(),
        );
    }

    /**
     * @param Book[] $books
     * @return self[]
     */
    public static function fromEntities(array $books): array
    {
        return array_map(self::fromEntity(...), $books);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isBorrowed' => $this->isBorrowed,
            'borrowedAt' => $this->borrowedAt,
            'borrowerCardNumber' => $this->borrowerCardNumber,
        ];
    }
}
