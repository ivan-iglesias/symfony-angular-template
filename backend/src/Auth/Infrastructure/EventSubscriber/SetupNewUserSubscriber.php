<?php

namespace App\Auth\Infrastructure\EventSubscriber;

use App\Auth\Domain\Entity\SecurityToken;
use App\Auth\Domain\Enum\SecurityTokenType;
use App\Auth\Domain\Event\UserRegisteredEvent;
use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\UserRegistrationNotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SetupNewUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SecurityTokenRepositoryInterface $tokenRepository,
        private UserRepositoryInterface $userRepository,
        private UserRegistrationNotifierInterface $emailService,
        private string $confirmationUrlPattern
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $this->userRepository->findById($event->userId);

        if (null === $user) {
            return;
        }

        $tokenValue = bin2hex(random_bytes(32));

        $securityToken = new SecurityToken(
            $user,
            $tokenValue,
            SecurityTokenType::CONFIRMATION
        );

        $this->tokenRepository->save($securityToken);

        $url = str_replace('{token}', $tokenValue, $this->confirmationUrlPattern);

        $this->emailService->sendConfirmationLink($user, $url);
    }
}
