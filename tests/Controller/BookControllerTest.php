<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class BookControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->truncateBooksTable();
    }

    // Czyści tabelę books przed każdym testem (brak dama/doctrine-test-bundle, więc ręcznie)
    private function truncateBooksTable(): void
    {
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('TRUNCATE books RESTART IDENTITY CASCADE');
    }

    private function requestJson(string $method, string $uri, array $payload = []): void
    {
        $this->client->request(
            $method,
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $payload === [] ? '' : json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    private function decodeResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    // POST /api/books — poprawne dane -> 201 i zwrócona książka
    public function testCreateBookReturns201(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Pan Tadeusz', 'author' => 'Adam Mickiewicz']);

        $this->assertResponseStatusCodeSame(201);
        $data = $this->decodeResponse();
        $this->assertSame(123456, $data['id']);
        $this->assertSame('Pan Tadeusz', $data['title']);
        $this->assertFalse($data['isBorrowed']);
    }

    // POST /api/books — duplikat numeru seryjnego -> 409
    public function testCreateBookDuplicateIdReturns409(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł A', 'author' => 'Autor A']);
        $this->assertResponseStatusCodeSame(201);

        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł B', 'author' => 'Autor B']);

        $this->assertResponseStatusCodeSame(409);
        $data = $this->decodeResponse();
        $this->assertSame(409, $data['code']);
    }

    // POST /api/books — niepoprawny (zbyt krótki) numer seryjny -> 400
    public function testCreateBookInvalidSerialNumberReturns400(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123, 'title' => 'Tytuł', 'author' => 'Autor']);

        $this->assertResponseStatusCodeSame(400);
    }

    // POST /api/books — brak wymaganych pól -> 400
    public function testCreateBookMissingFieldsReturns400(): void
    {
        $this->requestJson('POST', '/api/books', ['title' => 'Tylko tytuł']);

        $this->assertResponseStatusCodeSame(400);
    }

    // GET /api/books — zwraca listę wszystkich książek
    public function testListBooksReturnsAllBooks(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 111111, 'title' => 'Książka 1', 'author' => 'Autor 1']);
        $this->requestJson('POST', '/api/books', ['id' => 222222, 'title' => 'Książka 2', 'author' => 'Autor 2']);

        $this->client->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse();
        $this->assertCount(2, $data);
    }

    // DELETE /api/books/{id} — usunięcie istniejącej książki -> 204
    public function testDeleteBookReturns204(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł', 'author' => 'Autor']);

        $this->client->request('DELETE', '/api/books/123456');

        $this->assertResponseStatusCodeSame(204);
    }

    // DELETE /api/books/{id} — nieistniejąca książka -> 404
    public function testDeleteBookNotFoundReturns404(): void
    {
        $this->client->request('DELETE', '/api/books/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    // PATCH /api/books/{id}/status — oznaczenie jako wypożyczona
    public function testUpdateStatusToBorrowed(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł', 'author' => 'Autor']);

        $this->requestJson('PATCH', '/api/books/123456/status', ['status' => 'borrowed', 'borrowerCardNumber' => 654321]);

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse();
        $this->assertTrue($data['isBorrowed']);
        $this->assertSame(654321, $data['borrowerCardNumber']);
        $this->assertNotNull($data['borrowedAt']);
    }

    // PATCH /api/books/{id}/status — zwrot książki (available)
    public function testUpdateStatusToAvailable(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł', 'author' => 'Autor']);
        $this->requestJson('PATCH', '/api/books/123456/status', ['status' => 'borrowed', 'borrowerCardNumber' => 654321]);

        $this->requestJson('PATCH', '/api/books/123456/status', ['status' => 'available']);

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse();
        $this->assertFalse($data['isBorrowed']);
        $this->assertNull($data['borrowedAt']);
        $this->assertNull($data['borrowerCardNumber']);
    }

    // PATCH /api/books/{id}/status — nieistniejąca książka -> 404
    public function testUpdateStatusNotFoundReturns404(): void
    {
        $this->requestJson('PATCH', '/api/books/999999/status', ['status' => 'available']);

        $this->assertResponseStatusCodeSame(404);
    }

    // PATCH /api/books/{id}/status — niepoprawny numer karty -> 400
    public function testUpdateStatusInvalidCardNumberReturns400(): void
    {
        $this->requestJson('POST', '/api/books', ['id' => 123456, 'title' => 'Tytuł', 'author' => 'Autor']);

        $this->requestJson('PATCH', '/api/books/123456/status', ['status' => 'borrowed', 'borrowerCardNumber' => 1]);

        $this->assertResponseStatusCodeSame(400);
    }
}
