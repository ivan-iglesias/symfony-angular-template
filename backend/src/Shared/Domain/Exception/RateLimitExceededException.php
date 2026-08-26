<?php

namespace App\Shared\Domain\Exception;

final class RateLimitExceededException extends BusinessException
{
    public function __construct(
        private readonly int $retryAfterSeconds
    ) {
        // $message = sprintf(
        //     'Has superado el límite de peticiones. Por favor, reintenta en %d segundos.',
        //     $retryAfterSeconds
        // );

        parent::__construct(ApiErrorCode::TOO_MANY_REQUESTS);
    }

    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
