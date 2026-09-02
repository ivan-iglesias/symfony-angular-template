<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\LoginInput;
use App\Auth\Domain\Service\AuthServiceInterface;
use App\Auth\Domain\Service\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class LoginAction
{
    public function __construct(
        private AuthServiceInterface $authService,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private LoggerInterface $logger,
    ) {}

    public function execute(LoginInput $input): AuthResponse
    {
        $user = $this->authService->authenticate($input->email, $input->password);

        // if ($user->isBanned()) { throw new \Exception("Acceso denegado"); }

        // JWT Access Token
        $accessToken = $this->jwtManager->create($user);

        // Refresh Token
        $refreshToken = $this->refreshTokenGenerator->createForUser($user);

        $this->logger->info('User logged in successfully', [
            'email' => $user->getEmail()
        ]);

        return AuthResponse::create(
            token: $accessToken,
            refreshToken: $refreshToken,
            user: $user
        );
    }
}
