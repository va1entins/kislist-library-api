<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidCardNumberException extends \InvalidArgumentException implements ApiExceptionInterface
{
    public function getStatusCode(): int
    {
        return 400;
    }
}
