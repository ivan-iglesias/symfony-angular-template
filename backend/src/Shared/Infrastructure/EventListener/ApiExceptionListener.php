<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Infrastructure\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final readonly class ApiExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.environment%')]
        private string $environment
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // 1. Errores de Validación de DTOs (422)
        if ($exception instanceof ValidationException) {
            $errorCode = ApiErrorCode::VALIDATION_ERROR;

            $event->setResponse(ApiResponse::error(
                code: $errorCode->value,
                message: $exception->getMessage(),
                status: $errorCode->httpCode(),
                data: $exception->getErrors(),
            ));
            return;
        }

        // 2. Errores de Negocio/Dominio
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

        // 3. Errores de Symfony / HTTP (404, 405, 429, etc.)
        // TODO: Revisar match stados
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $code = match ($status) {
                404 => 'RESOURCE_NOT_FOUND',
                405 => 'METHOD_NOT_ALLOWED',
                403 => 'ACCESS_DENIED',
                429 => 'TOO_MANY_REQUESTS',
                default => 'HTTP_ERROR',
            };

            $event->setResponse(ApiResponse::error(
                code: $code,
                message: $exception->getMessage(),
                status: $status,
                data: null,
            ));

            return;
        }

        // 4. Fallos Críticos no controlados (500)
        $correlationId = (string) $request->attributes->get('correlation_id', '');

        $this->logger->critical(sprintf('Excepción no capturada [%s]: %s', $correlationId, $exception->getMessage()), [
            'trace' => $exception->getTraceAsString()
        ]);

        $message = ($this->environment === 'dev')
            ? 'DEBUG: ' . $exception->getMessage()
            : 'Error interno del servidor';

        $event->setResponse(ApiResponse::critical($message));
    }
}
