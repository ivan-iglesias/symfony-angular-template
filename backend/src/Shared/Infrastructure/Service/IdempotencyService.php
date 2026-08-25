<?php

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Exception\BusinessErrorCode;
use App\Shared\Infrastructure\Response\ApiResponse;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyService
{
    public function __construct(private readonly CacheItemPoolInterface $cache) {}

    public function getSavedResponse(string $key, string $fingerprint): ?Response
    {
        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        $data = $item->get();
        if (!is_array($data)) {
            return null;
        }

        if (($data['fingerprint'] ?? null) !== $fingerprint) {
            $errorCode = BusinessErrorCode::IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD;

            return ApiResponse::error(
                $errorCode->value,
                $errorCode->defaultMessage(),
                $errorCode->httpCode()
            );
        }

        return new Response(
            (string) ($data['content'] ?? ''),
            (int) ($data['status_code'] ?? 200),
            array_merge($data['headers'] ?? [], ['Idempotency-Status' => 'replayed'])
        );
    }

    public function saveResponse(string $key, string $fingerprint, Response $response, int $ttl): void
    {
        if ($response->getStatusCode() >= 500) {
            return;
        }

        $item = $this->cache->getItem($key);
        $item->set([
            'fingerprint' => $fingerprint,
            'status_code' => $response->getStatusCode(),
            'content' => (string) $response->getContent(),
            'headers' => [
                'Content-Type' => $response->headers->get('Content-Type', 'application/json'),
            ],
        ]);
        $item->expiresAfter($ttl);
        $this->cache->save($item);
    }
}
