<?php

declare(strict_types=1);

namespace App\Exception;

final class InvalidSerialNumberException extends \InvalidArgumentException implements ApiExceptionInterface
{
    #[\Override]
    public function getStatusCode(): int
    {
        return 400;
    }
}
