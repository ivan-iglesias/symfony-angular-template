<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Infrastructure\Logging\CorrelationIdProcessor;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

final class CorrelationIdListener
{
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 255)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $correlationId = $request->headers->get('X-Correlation-ID') ?? Uuid::v4()->toRfc4122();

        $request->attributes->set('correlation_id', $correlationId);

        // Fijamos el ID en el procesador de Monolog para todo el ciclo de vida del proceso
        CorrelationIdProcessor::setCorrelationId($correlationId);
    }

    #[AsEventListener(event: KernelEvents::RESPONSE, priority: -255)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $correlationId = (string) $request->attributes->get('correlation_id', '');

        if ($correlationId === '') {
            return;
        }

        $response->headers->set('X-Correlation-ID', $correlationId);

        // Si es JsonResponse, asegura que el payload lleve el correlation_id si estaba a null
        // ApiResponse esta comentado correlation_id para solo mandarla por cabecera
        if ($response instanceof JsonResponse) {
            $data = json_decode((string) $response->getContent(), true);
            if (is_array($data) && array_key_exists('correlation_id', $data) && $data['correlation_id'] === null) {
                $data['correlation_id'] = $correlationId;
                $response->setData($data);
            }
        }
    }
}
