<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiExceptionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Globalny handler wyjątków — ujednolicony format JSON dla całego API.
 */
final class ExceptionSubscriber implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        [$message, $statusCode] = match (true) {
            $exception instanceof ApiExceptionInterface => [$exception->getMessage(), $exception->getStatusCode()],
            $exception instanceof UniqueConstraintViolationException => ['Książka o podanym numerze seryjnym już istnieje', 409],
            // Kolejność ma znaczenie: HttpExceptionInterface musi być sprawdzony przed default,
            // inaczej natywne wyjątki Symfony (np. 404 dla nieznanej trasy) trafiają do 500
            $exception instanceof HttpExceptionInterface => [$exception->getMessage(), $exception->getStatusCode()],
            default => ['Wewnętrzny błąd serwera', 500],
        };

        $event->setResponse(new JsonResponse(
            ['error' => $message, 'code' => $statusCode],
            $statusCode,
        ));
    }
}
