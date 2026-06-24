<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\CreateBookRequest;
use App\Dto\Request\UpdateBookStatusRequest;
use App\Dto\Response\BookResponse;
use App\Exception\InvalidRequestException;
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
        $createRequest = CreateBookRequest::fromArray($this->decodeJson($request));
        $book = $this->bookService->createBook($createRequest);

        return new JsonResponse(BookResponse::fromEntity($book)->toArray(), 201);
    }

    #[Route('/{id}', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): JsonResponse
    {
        $this->bookService->removeBook($id);

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
        $statusRequest = UpdateBookStatusRequest::fromArray($this->decodeJson($request));
        $book = $this->bookService->updateStatus($id, $statusRequest);

        return new JsonResponse(BookResponse::fromEntity($book)->toArray(), 200);
    }

    /**
     * Dekoduje JSON z requestu i mapuje błędy na InvalidRequestException
     * (obsłużony przez globalny ExceptionSubscriber).
     */
    private function decodeJson(Request $request): array
    {
        try {
            return json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidRequestException('Niepoprawny format JSON', previous: $e);
        }
    }
}
