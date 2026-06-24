<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Request\CreateBookRequest;
use App\Dto\Request\UpdateBookStatusRequest;
use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Exception\InvalidCardNumberException;
use App\Exception\InvalidSerialNumberException;
use App\Repository\BookRepository;
use App\ValueObject\CardNumber;
use App\ValueObject\SerialNumber;

final readonly class BookService
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {
    }

    // Konwersja int -> VO i walidacja odbywają się w Service (nie w Entity),
    // bo Doctrine mapuje kolumnę jako integer — VO nie jest typem Doctrine
    public function createBook(CreateBookRequest $request): Book
    {
        try {
            $serialNumber = new SerialNumber($request->id);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidSerialNumberException($e->getMessage(), previous: $e);
        }

        $book = new Book($serialNumber->toInt(), $request->title, $request->author);
        $this->bookRepository->save($book);

        return $book;
    }

    public function removeBook(int $id): void
    {
        $book = $this->getBookOrFail($id);
        $this->bookRepository->remove($book);
    }

    /**
     * @return Book[]
     */
    public function findAllBooks(): array
    {
        return $this->bookRepository->findAllBooks();
    }

    public function updateStatus(int $id, UpdateBookStatusRequest $request): Book
    {
        $book = $this->getBookOrFail($id);

        if ($request->isBorrowed) {
            try {
                $cardNumber = new CardNumber($request->borrowerCardNumber);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidCardNumberException($e->getMessage(), previous: $e);
            }

            $book->setIsBorrowed(true);
            $book->setBorrowedAt(new \DateTimeImmutable());
            $book->setBorrowerCardNumber($cardNumber->toInt());
        } else {
            // Zwrot książki — czyścimy dane wypożyczenia
            $book->setIsBorrowed(false);
            $book->setBorrowedAt(null);
            $book->setBorrowerCardNumber(null);
        }

        $this->bookRepository->save($book);

        return $book;
    }

    private function getBookOrFail(int $id): Book
    {
        $book = $this->bookRepository->find($id);

        if (null === $book) {
            throw new BookNotFoundException($id);
        }

        return $book;
    }
}
