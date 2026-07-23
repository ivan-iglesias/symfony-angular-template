<?php

namespace App\Auth\Application\Actions;


use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Shared\Domain\Exception\BusinessErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use App\Shared\Domain\Service\LockServiceInterface;

class ConfirmAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SecurityTokenRepositoryInterface $tokenRepository,
        private LockServiceInterface $lockService
    ) {}

    public function execute(string $tokenValue): void
    {
        $executed = $this->lockService->acquireAndExecute(
            'token_confirm_' . $tokenValue,
            function () use ($tokenValue) {
                $token = $this->tokenRepository->findByValue($tokenValue);

                if (!$token || !$token->isValid()) {
                    throw new BusinessException(BusinessErrorCode::AUTH_INVALID_TOKEN);
                }

                $user = $token->getUser();
                $user->activate();

                $this->userRepository->save($user);
                $this->tokenRepository->delete($token);
            }
        );

        if (!$executed) {
            throw new BusinessException(BusinessErrorCode::RESOURCE_LOCKED);
        }
    }
}
