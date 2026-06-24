<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidSerialNumberException extends \InvalidArgumentException implements ApiExceptionInterface
{
    public function getStatusCode(): int
    {
        return 400;
    }
}
