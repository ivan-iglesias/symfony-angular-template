<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\PasswordlessLoginInput;
use App\Auth\Domain\Entity\SecurityToken;
use App\Auth\Domain\Enum\SecurityTokenType;
use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\UserRegistrationNotifierInterface;
use App\Shared\Domain\Service\CodeGeneratorInterface;
use Psr\Log\LoggerInterface;

final readonly class PasswordlessLoginAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SecurityTokenRepositoryInterface $tokenRepository,
        private readonly CodeGeneratorInterface $codeGenerator,
        private readonly UserRegistrationNotifierInterface $notifier,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(PasswordlessLoginInput $input): void
    {
        $user = $this->userRepository->findByEmail($input->email);

        if (!$user || !$user->isActive()) return;

        $code = $this->codeGenerator->generateNumeric();

        $token = new SecurityToken(
            $user,
            $code,
            SecurityTokenType::TYPE_LOGIN,
            1
        );

        $this->tokenRepository->save($token);

        $this->notifier->sendLoginCode($user, $code);

        $this->logger->info('Passwordless login code requested', [
            'email' => $user->getEmail(),
        ]);
    }
}
