<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\Request\CreateBookRequest;
use App\Dto\Request\UpdateBookStatusRequest;
use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Exception\InvalidCardNumberException;
use App\Exception\InvalidSerialNumberException;
use App\Repository\BookRepository;
use App\Service\BookService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookServiceTest extends TestCase
{
    private BookRepository&MockObject $repository;
    private BookService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BookRepository::class);
        $this->service = new BookService($this->repository);
    }

    // createBook: poprawne dane -> książka zapisana i zwrócona
    public function testCreateBookSavesAndReturnsBook(): void
    {
        $request = new CreateBookRequest(id: 123456, title: 'Pan Tadeusz', author: 'Adam Mickiewicz');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Book $book) => $book->getId() === 123456));

        $book = $this->service->createBook($request);

        $this->assertSame(123456, $book->getId());
        $this->assertSame('Pan Tadeusz', $book->getTitle());
        $this->assertSame('Adam Mickiewicz', $book->getAuthor());
    }

    // createBook: niepoprawny (zbyt krótki) numer seryjny -> wyjątek, brak zapisu
    public function testCreateBookThrowsOnInvalidSerialNumber(): void
    {
        $request = new CreateBookRequest(id: 123, title: 'Tytuł', author: 'Autor');

        $this->repository->expects($this->never())->method('save');

        $this->expectException(InvalidSerialNumberException::class);
        $this->service->createBook($request);
    }

    // removeBook: książka istnieje -> usunięcie
    public function testRemoveBookRemovesExistingBook(): void
    {
        $book = new Book(123456, 'Tytuł', 'Autor');

        $this->repository->expects($this->once())->method('find')->with(123456)->willReturn($book);
        $this->repository->expects($this->once())->method('remove')->with($book);

        $this->service->removeBook(123456);
    }

    // removeBook: książka nie istnieje -> BookNotFoundException
    public function testRemoveBookThrowsWhenNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->willReturn(null);
        $this->repository->expects($this->never())->method('remove');

        $this->expectException(BookNotFoundException::class);
        $this->service->removeBook(999999);
    }

    // findAllBooks: delegacja do repozytorium
    public function testFindAllBooksReturnsRepositoryResult(): void
    {
        $books = [new Book(123456, 'A', 'B'), new Book(654321, 'C', 'D')];
        $this->repository->expects($this->once())->method('findAllBooks')->willReturn($books);

        $this->assertSame($books, $this->service->findAllBooks());
    }

    // updateStatus: wypożyczenie z poprawnym numerem karty
    public function testUpdateStatusBorrowsBookWithValidCardNumber(): void
    {
        $book = new Book(123456, 'Tytuł', 'Autor');
        $this->repository->expects($this->once())->method('find')->with(123456)->willReturn($book);
        $this->repository->expects($this->once())->method('save')->with($book);

        $request = new UpdateBookStatusRequest(isBorrowed: true, borrowerCardNumber: 654321);
        $updated = $this->service->updateStatus(123456, $request);

        $this->assertTrue($updated->isBorrowed());
        $this->assertSame(654321, $updated->getBorrowerCardNumber());
        $this->assertInstanceOf(\DateTimeImmutable::class, $updated->getBorrowedAt());
    }

    // updateStatus: wypożyczenie z niepoprawnym numerem karty -> wyjątek
    public function testUpdateStatusThrowsOnInvalidCardNumber(): void
    {
        $book = new Book(123456, 'Tytuł', 'Autor');
        $this->repository->expects($this->once())->method('find')->willReturn($book);
        $this->repository->expects($this->never())->method('save');

        $request = new UpdateBookStatusRequest(isBorrowed: true, borrowerCardNumber: 1);

        $this->expectException(InvalidCardNumberException::class);
        $this->service->updateStatus(123456, $request);
    }

    // updateStatus: zwrot książki -> czyszczenie danych wypożyczenia
    public function testUpdateStatusReturnsBookAndClearsData(): void
    {
        $book = new Book(123456, 'Tytuł', 'Autor');
        $book->setIsBorrowed(true);
        $book->setBorrowedAt(new \DateTimeImmutable());
        $book->setBorrowerCardNumber(654321);

        $this->repository->expects($this->once())->method('find')->willReturn($book);
        $this->repository->expects($this->once())->method('save')->with($book);

        $request = new UpdateBookStatusRequest(isBorrowed: false, borrowerCardNumber: null);
        $updated = $this->service->updateStatus(123456, $request);

        $this->assertFalse($updated->isBorrowed());
        $this->assertNull($updated->getBorrowedAt());
        $this->assertNull($updated->getBorrowerCardNumber());
    }

    // updateStatus: książka nie istnieje -> BookNotFoundException
    public function testUpdateStatusThrowsWhenBookNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->willReturn(null);

        $request = new UpdateBookStatusRequest(isBorrowed: false, borrowerCardNumber: null);

        $this->expectException(BookNotFoundException::class);
        $this->service->updateStatus(999999, $request);
    }
}
