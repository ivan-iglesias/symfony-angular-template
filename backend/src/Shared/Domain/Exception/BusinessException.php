<?php

namespace App\Shared\Domain\Exception;

class BusinessException extends \DomainException
{
    public function __construct(
        private readonly ApiErrorCode $errorCode,
        ?string $message = null
    ) {
        parent::__construct(
            $message ?? $this->errorCode->defaultMessage(),
            $this->errorCode->httpCode()
        );
    }

    public function getErrorCode(): ApiErrorCode
    {
        return $this->errorCode;
    }
}
