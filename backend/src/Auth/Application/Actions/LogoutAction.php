<?php

namespace App\Auth\Application\Actions;

use App\Auth\Domain\Service\RefreshTokenGeneratorInterface;

final readonly class LogoutAction
{
    public function __construct(
        private RefreshTokenGeneratorInterface $refreshTokenGenerator
    ) {}

    public function execute(?string $refreshToken): void
    {
        if (!$refreshToken) {
            return;
        }

        $this->refreshTokenGenerator->revokeToken($refreshToken);
    }
}
