<?php

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Service\RefreshTokenGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class RedisRefreshTokenGenerator implements RefreshTokenGeneratorInterface
{
    private const TOKEN_PREFIX = 'refresh_token:';
    private const USER_SET_PREFIX = 'user_tokens:';

    public function __construct(
        private \Redis $redis,
        private int $ttlSeconds
    ) {}

    public function createForUser(UserInterface $user): string
    {
        $tokenString = bin2hex(random_bytes(32));
        $tokenKey = self::TOKEN_PREFIX . $tokenString;
        $userSetKey = self::USER_SET_PREFIX . $user->getUserIdentifier();

        $this->redis->setex($tokenKey, $this->ttlSeconds, $user->getUserIdentifier());
        $this->redis->sAdd($userSetKey, $tokenKey);

        return $tokenString;
    }
}
