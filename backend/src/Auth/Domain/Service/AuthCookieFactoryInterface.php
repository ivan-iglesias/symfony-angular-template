<?php

namespace App\Auth\Domain\Service;

// No estás acoplando lógica de framework (Kernel, Contenedor, ORM),
// sino un Value Object inmutable de transporte HTTP.
use Symfony\Component\HttpFoundation\Cookie;

interface AuthCookieFactoryInterface
{
    public const REFRESH_TOKEN_COOKIE_NAME = 'refresh_token';

    public const AUTH_PATH = '/api/v1/auth';

    public function createRefreshTokenCookie(string $refreshToken): Cookie;

    public function createLogoutCookie(): Cookie;
}
