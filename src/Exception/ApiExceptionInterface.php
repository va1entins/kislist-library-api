<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Interfejs dla wyjątków domenowych, które mają być mapowane
 * na konkretny kod HTTP przez globalny ExceptionSubscriber.
 */
interface ApiExceptionInterface
{
    public function getStatusCode(): int;
}
