<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\LoginInput;
use App\Auth\Domain\Service\AuthServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class LoginAction
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(LoginInput $input): AuthResponse
    {
        $user = $this->authService->authenticate($input->email, $input->password);

        $token = $this->jwtManager->create($user);

        // if ($userDto->isBanned()) { throw new \Exception("Acceso denegado"); }

        $this->logger->info('User logged in successfully', [
            'email' => $user->getEmail()
        ]);

        return AuthResponse::fromUser($token, $user);
    }
}
