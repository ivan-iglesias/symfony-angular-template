<?php

namespace App\Auth\Infrastructure\Service;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\AuthServiceInterface;
use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function authenticate(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new BusinessException(ApiErrorCode::AUTH_INVALID_CREDENTIALS);
        }

        if (!$user->isActive()) {
            throw new BusinessException(ApiErrorCode::AUTH_USER_INACTIVE);
        }

        return $user;
    }
}
