<?php

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class AuthCookieFactory implements AuthCookieFactoryInterface
{
    public function __construct(
        private int $ttlSeconds
    ) {}

    public function createRefreshTokenCookie(string $refreshToken): Cookie
    {
        return Cookie::create(
            name: self::REFRESH_TOKEN_COOKIE_NAME,
            value: $refreshToken,
            expire: time() + $this->ttlSeconds,
            path: self::AUTH_PATH,
            secure: true,
            httpOnly: true,
            sameSite: Cookie::SAMESITE_STRICT
        );
    }

    public function createLogoutCookie(): Cookie
    {
        return Cookie::create(
            name: self::REFRESH_TOKEN_COOKIE_NAME,
            value: '',
            expire: 1, // Caduca la cookie de forma inmediata en el navegador
            path: self::AUTH_PATH,
            secure: true,
            httpOnly: true,
            sameSite: Cookie::SAMESITE_STRICT
        );
    }
}
