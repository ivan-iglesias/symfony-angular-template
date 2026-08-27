<?php

namespace App\Shared\Infrastructure\Security\RateLimiter;

use App\Shared\Domain\Exception\RateLimitExceededException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class RateLimiterService
{
    public function __construct(
        private readonly RateLimiterFactory $apiLimiter
    ) {}

    public function check(Request $request, ?string $customKey = null): void
    {
        $key = $customKey ?? $request->getClientIp() ?? 'anonymous';

        $limiter = $this->apiLimiter->create($key);

        $limit = $limiter->consume(); // 1 por defecto sin argumentos

        if (!$limit->isAccepted()) {
            $retryAfterSeconds = $limit->getRetryAfter()->getTimestamp() - time();
            throw new RateLimitExceededException(max(1, $retryAfterSeconds));
        }
    }
}
