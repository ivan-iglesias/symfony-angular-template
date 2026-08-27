<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\PasswordlessLoginVerifyInput;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use App\Auth\Domain\Enum\SecurityTokenType;
use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class PasswordlessLoginVerifyAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SecurityTokenRepositoryInterface $tokenRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(PasswordlessLoginVerifyInput $input): AuthResponse
    {
        $user = $this->userRepository->findByEmail($input->email);

        if (!$user) {
            throw new BusinessException(ApiErrorCode::AUTH_USER_NOT_FOUND);
        }

        $securityToken = $this->tokenRepository->findByTokenAndUser(
            $input->code,
            $user,
            SecurityTokenType::TYPE_LOGIN
        );

        if (!$securityToken) {
            throw new BusinessException(ApiErrorCode::AUTH_INVALID_CODE);
        }

        $this->tokenRepository->delete($securityToken);

        if (!$securityToken->isValid()) {
            throw new BusinessException(ApiErrorCode::AUTH_INVALID_CODE);
        }

        $token = $this->jwtManager->create($user);

        $this->logger->info('User logged in via security code', [
            'email' => $user->getEmail()
        ]);

        return AuthResponse::fromUser($token, $user);
    }
}
