<?php

declare(strict_types=1);

namespace App\Exception;

final class BookNotFoundException extends \RuntimeException implements ApiExceptionInterface
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Książka o numerze seryjnym %d nie została znaleziona', $id));
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return 404;
    }
}
