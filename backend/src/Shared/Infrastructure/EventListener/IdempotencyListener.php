<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Exception\BusinessErrorCode;
use App\Shared\Infrastructure\Attribute\ControllerAttributeInspector;
use App\Shared\Infrastructure\Attribute\Idempotent;
use App\Shared\Infrastructure\Response\ApiResponse;
use App\Shared\Infrastructure\Service\IdempotencyService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class IdempotencyListener
{
    private const ATTR_CACHE_KEY = '_idempotency_cache_key';
    private const ATTR_FINGERPRINT = '_idempotency_fingerprint';
    private const ATTR_TTL = '_idempotency_ttl';
    private const ATTR_LOCK = '_idempotency_lock';

    public function __construct(
        private readonly ControllerAttributeInspector $attributeInspector,
        private readonly IdempotencyService $idempotencyService,
        private readonly LockFactory $lockFactory
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        /** @var Idempotent|null $idempotentAttribute */
        $idempotentAttribute = $this->attributeInspector->getMethodAttribute($request, Idempotent::class);

        if ($idempotentAttribute === null) {
            return;
        }

        $idempotencyKey = trim((string) $request->headers->get('Idempotency-Key', ''));

        if ($idempotencyKey === '') {
            return;
        }

        $fingerprint = hash('sha256', $request->getMethod() . $request->getPathInfo() . $request->getContent());
        $cacheKey = 'idemp_' . hash('sha256', $idempotencyKey . $request->getClientIp());

        $savedResponse = $this->idempotencyService->getSavedResponse($cacheKey, $fingerprint);

        if ($savedResponse !== null) {
            $event->setResponse($savedResponse);
            return;
        }

        $lock = $this->lockFactory->createLock('lock_' . $cacheKey, 5.0);

        if (!$lock->acquire()) {
            $errorCode = BusinessErrorCode::IDEMPOTENCY_IN_PROGRESS;

            $event->setResponse(ApiResponse::error(
                $errorCode->value,
                $errorCode->defaultMessage(),
                $errorCode->httpCode()
            ));

            return;
        }

        // Mantenemos el Lock activo hasta onKernelResponse
        $request->attributes->set(self::ATTR_LOCK, $lock);
        $request->attributes->set(self::ATTR_CACHE_KEY, $cacheKey);
        $request->attributes->set(self::ATTR_FINGERPRINT, $fingerprint);
        $request->attributes->set(self::ATTR_TTL, $idempotentAttribute->ttlSeconds);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $cacheKey = (string) $request->attributes->get(self::ATTR_CACHE_KEY, '');

        if ($cacheKey === '') {
            return;
        }

        /** @var LockInterface|null $lock */
        $lock = $request->attributes->get(self::ATTR_LOCK);

        try {
            $fingerprint = (string) $request->attributes->get(self::ATTR_FINGERPRINT, '');
            $ttl = (int) $request->attributes->get(self::ATTR_TTL, 86400);
            $response = $event->getResponse();

            $this->idempotencyService->saveResponse($cacheKey, $fingerprint, $response, $ttl);
            $response->headers->set('Idempotency-Status', 'stored');
        } finally {
            $lock?->release();
        }
    }
}
