<?php

declare(strict_types=1);

namespace App\Exception;

class BookNotFoundException extends \RuntimeException implements ApiExceptionInterface
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Książka o numerze seryjnym %d nie została znaleziona', $id));
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}
