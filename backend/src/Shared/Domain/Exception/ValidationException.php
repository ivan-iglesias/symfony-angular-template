<?php

namespace App\Shared\Domain\Exception;

final class ValidationException extends \DomainException
{
    public function __construct(
        private readonly array $errors = [],
        ?string $message = null
    ) {
        $errorCode = ApiErrorCode::VALIDATION_ERROR;

        parent::__construct(
            $message ?? $errorCode->defaultMessage(),
            $errorCode->httpCode()
        );
    }

    public function getErrorCode(): ApiErrorCode
    {
        return ApiErrorCode::VALIDATION_ERROR;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
