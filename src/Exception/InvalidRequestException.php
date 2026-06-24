<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Wyjątek dla błędów parsowania/walidacji danych wejściowych
 * (niepoprawny JSON, brakujące pola w DTO).
 */
final class InvalidRequestException extends \InvalidArgumentException implements ApiExceptionInterface
{
    #[\Override]
    public function getStatusCode(): int
    {
        return 400;
    }
}
