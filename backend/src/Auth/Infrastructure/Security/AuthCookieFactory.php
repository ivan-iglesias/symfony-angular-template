<?php

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class AuthCookieFactory implements AuthCookieFactoryInterface
{
    private const REFRESH_TOKEN_COOKIE_NAME = 'refresh_token';
    private const AUTH_PATH = '/api/auth';

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
            expire: 1,
            path: self::AUTH_PATH,
            secure: true,
            httpOnly: true,
            sameSite: Cookie::SAMESITE_STRICT
        );
    }
}
