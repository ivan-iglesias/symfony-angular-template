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

    public function validateAndGetUser(string $refreshToken): ?string
    {
        $tokenKey = self::TOKEN_PREFIX . $refreshToken;

        $userIdentifier = $this->redis->get($tokenKey);

        if (!$userIdentifier || !is_string($userIdentifier)) {
            return null;
        }

        return $userIdentifier;
    }

    public function revokeToken(string $refreshToken): void
    {
        $tokenKey = self::TOKEN_PREFIX . $refreshToken;

        $userIdentifier = $this->redis->get($tokenKey);

        if ($userIdentifier && is_string($userIdentifier)) {
            $userSetKey = self::USER_SET_PREFIX . $userIdentifier;
            $this->redis->sRem($userSetKey, $tokenKey);
        }

        $this->redis->del($tokenKey);
    }

    public function revokeAllForUser(UserInterface $user): void
    {
        $userSetKey = self::USER_SET_PREFIX . $user->getUserIdentifier();

        $tokens = $this->redis->sMembers($userSetKey);

        if (!empty($tokens)) {
            $this->redis->del(...$tokens);
        }

        $this->redis->del([$userSetKey]);
    }
}
