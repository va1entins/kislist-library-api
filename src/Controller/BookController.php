<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\CreateBookRequest;
use App\Dto\Request\UpdateBookStatusRequest;
use App\Dto\Response\BookResponse;
use App\Exception\BookNotFoundException;
use App\Exception\InvalidCardNumberException;
use App\Exception\InvalidSerialNumberException;
use App\Service\BookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/api/books')]
final class BookController
{
    public function __construct(
        private readonly BookService $bookService,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $createRequest = CreateBookRequest::fromArray($data);
        } catch (\JsonException|\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        try {
            $book = $this->bookService->createBook($createRequest);
        } catch (InvalidSerialNumberException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(BookResponse::fromEntity($book)->toArray(), 201);
    }

    #[Route('/{id}', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): JsonResponse
    {
        try {
            $this->bookService->removeBook($id);
        } catch (BookNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(null, 204);
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $books = $this->bookService->findAllBooks();
        $responses = array_map(
            static fn (BookResponse $r) => $r->toArray(),
            BookResponse::fromEntities($books),
        );

        return new JsonResponse($responses, 200);
    }

    #[Route('/{id}/status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        // Dekodowanie i walidacja danych wejściowych
        try {
            $data = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $statusRequest = UpdateBookStatusRequest::fromArray($data);
        } catch (\JsonException|\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        try {
            $book = $this->bookService->updateStatus($id, $statusRequest);
        } catch (BookNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (InvalidCardNumberException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(BookResponse::fromEntity($book)->toArray(), 200);
    }
}
