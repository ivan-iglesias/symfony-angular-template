<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\PasswordlessLoginInput;
use App\Auth\Domain\Entity\SecurityToken;
use App\Auth\Domain\Enum\SecurityTokenType;
use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\LoginCodeGenerator;
use App\Auth\Domain\Service\UserRegistrationNotifierInterface;

class PasswordlessLoginAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SecurityTokenRepositoryInterface $tokenRepository,
        private LoginCodeGenerator $codeGenerator,
        private UserRegistrationNotifierInterface $notifier
    ) {}

    public function execute(PasswordlessLoginInput $input): void
    {
        $user = $this->userRepository->findByEmail($input->email);

        if (!$user || !$user->isActive()) return;

        $code = $this->codeGenerator->generate();

        $token = new SecurityToken(
            $user,
            $code,
            SecurityTokenType::TYPE_LOGIN,
            1
        );

        $this->tokenRepository->save($token);

        $this->notifier->sendLoginCode($user, $code);
    }
}
