<?php

namespace App\Shared\Infrastructure\Security\RateLimiter;

use App\Shared\Domain\Exception\RateLimitExceededException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Service\Attribute\Required;

trait HasRateLimiterTrait
{
    protected RateLimiterFactory $apiLimiter;

    #[Required]
    public function setApiLimiter(
        #[Autowire(service: 'limiter.api_limiter')] RateLimiterFactory $apiLimiter
    ): void {
        $this->apiLimiter = $apiLimiter;
    }

    protected function checkRateLimit(
        Request $request,
        ?string $customKey = null,
        ?RateLimiterFactory $customLimiter = null
    ): void {
        $limiterFactory = $customLimiter ?? $this->apiLimiter;

        $key = $customKey ?? $request->getClientIp() ?? 'anonymous';

        $limiter = $limiterFactory->create($key);

        $limit = $limiter->consume(); // 1 por defecto sin argumentos

        if (!$limit->isAccepted()) {
            $retryAfterSeconds = $limit->getRetryAfter()->getTimestamp() - time();

            throw new RateLimitExceededException(max(1, $retryAfterSeconds));
        }
    }
}
