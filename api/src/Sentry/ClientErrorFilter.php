<?php

declare(strict_types=1);

namespace App\Sentry;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface as ApiPlatformHttpException;
use ApiPlatform\Metadata\Exception\InvalidArgumentException as ApiPlatformInvalidArgumentException;
use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use Sentry\Event;
use Sentry\EventHint;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;

/**
 * Sentry "before_send" callback: client errors (4xx) are not reported, only 5xx and unexpected exceptions.
 */
final class ClientErrorFilter
{
    public function __invoke(Event $event, ?EventHint $hint = null): ?Event
    {
        $exception = $hint?->exception;

        if (null !== $exception && $this->isClientError($exception)) {
            return null;
        }

        return $event;
    }

    private function isClientError(\Throwable $exception): bool
    {
        // Mapped to 4xx by API Platform (invalid body, validation errors, security, unknown resource...)
        if ($exception instanceof AccessDeniedException
            || $exception instanceof AuthenticationException
            || $exception instanceof ValidationException
            || $exception instanceof SerializerException
            || $exception instanceof ItemNotFoundException
            || $exception instanceof ApiPlatformInvalidArgumentException
        ) {
            return true;
        }

        $status = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ApiPlatformHttpException => $exception->getStatusCode(),
            $exception instanceof ProblemExceptionInterface => $exception->getStatus(),
            default => null,
        };

        return null !== $status && $status >= 400 && $status < 500;
    }
}
