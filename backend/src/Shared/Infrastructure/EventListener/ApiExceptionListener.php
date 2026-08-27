<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Infrastructure\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final readonly class ApiExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private string $environment
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $isDev = $this->environment === 'dev' || $this->environment === 'test';

        // -------------------------------------------------------------
        // 1. Errores de Validación de DTOs
        // -------------------------------------------------------------
        if ($exception instanceof ValidationException) {
            $errorCode = $exception->getErrorCode();

            $event->setResponse(ApiResponse::error(
                code: $errorCode->value,
                message: $exception->getMessage(),
                status: $errorCode->httpCode(),
                data: $exception->getErrors(),
            ));
            return;
        }

        // -------------------------------------------------------------
        // 2. Errores de Negocio/Dominio
        // -------------------------------------------------------------
        if ($exception instanceof BusinessException) {
            $errorCode = $exception->getErrorCode();

            $event->setResponse(ApiResponse::error(
                code: $errorCode->value,
                message: $exception->getMessage(),
                status: $errorCode->httpCode(),
                data: null
            ));
            return;
        }

        // -------------------------------------------------------------
        // 3. Errores de Symfony / HTTP (404, 405, 429, etc.)
        // -------------------------------------------------------------
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();

            $errorCode = match ($status) {
                404 => ApiErrorCode::RESOURCE_NOT_FOUND,
                405 => ApiErrorCode::METHOD_NOT_ALLOWED,
                403 => ApiErrorCode::ACCESS_DENIED,
                429 => ApiErrorCode::TOO_MANY_REQUESTS,
                default => ApiErrorCode::HTTP_ERROR,
            };

            $message = $isDev && !empty($exception->getMessage())
                ? $exception->getMessage()
                : $errorCode->defaultMessage();

            $event->setResponse(ApiResponse::error(
                code: $errorCode->value,
                message: $message,
                status: $status,
                data: null,
            ));
            return;
        }

        // -------------------------------------------------------------
        // 4. Fallos Críticos no controlados (500)
        // -------------------------------------------------------------
        $correlationId = (string) $request->attributes->get('correlation_id', '');

        $this->logger->critical(sprintf('Unhandled exception: %s', $correlationId, $exception->getMessage()), [
            'exception' => $exception,
        ]);

        $message = $isDev
            ? 'DEBUG: ' . $exception->getMessage()
            : 'Error interno del servidor';

        $event->setResponse(ApiResponse::critical($message));
    }
}
