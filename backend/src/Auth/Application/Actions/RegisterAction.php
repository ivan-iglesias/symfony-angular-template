<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\RegisterInput;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Event\UserRegisteredEvent;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function execute(RegisterInput $input): void
    {
        if ($this->userRepository->existsByEmail($input->email)) {
            throw new BusinessException(ApiErrorCode::AUTH_USER_ALREADY_EXISTS);
        }

        $user = new User();

        $hashedPassword = $this->passwordHasher->hashPassword($user, $input->password);

        $user
            ->setName($input->name)
            ->setLastName($input->lastName)
            ->setEmail($input->email)
            ->setPassword($hashedPassword);

        $this->userRepository->save($user);

        $this->eventDispatcher->dispatch(
            new UserRegisteredEvent($user->getId(), $user->getEmail(), $user->getName())
        );
    }
}
