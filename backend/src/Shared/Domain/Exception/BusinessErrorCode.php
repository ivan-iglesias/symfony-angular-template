<?php

namespace App\Shared\Domain\Exception;

enum BusinessErrorCode: string
{
    case AUTH_INVALID_CODE = 'AUTH_INVALID_CODE';
    case AUTH_INVALID_TOKEN = 'AUTH_INVALID_TOKEN';
    case AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    case AUTH_USER_NOT_FOUND = 'AUTH_USER_NOT_FOUND';
    case AUTH_USER_ALREADY_EXISTS = 'AUTH_USER_ALREADY_EXISTS';
    case AUTH_USER_INACTIVE = 'AUTH_USER_INACTIVE';
    case RESOURCE_LOCKED = 'RESOURCE_LOCKED';

    public function defaultMessage(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CODE => 'El código es incorrecto o ha caducado.',
            self::AUTH_INVALID_TOKEN => 'El token proporcionado no existe o ha expirado.',
            self::AUTH_INVALID_CREDENTIALS => 'Credenciales incorrectas.',
            self::AUTH_USER_NOT_FOUND => 'Usuario no encontrado.',
            self::AUTH_USER_ALREADY_EXISTS => 'Email ya registrado en el sistema.',
            self::AUTH_USER_INACTIVE => 'La cuenta de usuario no está activa.',
            self::RESOURCE_LOCKED => 'La solicitud ya se está procesando en otra sesión.',
        };
    }

    public function httpCode(): int
    {
        return match ($this) {
            // 401 Unauthorized: Cuando las credenciales (código o token) fallan.
            self::AUTH_INVALID_CODE,
            self::AUTH_INVALID_TOKEN,
            self::AUTH_INVALID_CREDENTIALS => 401,

            // 403 Forbidden: El usuario existe pero tiene el paso prohibido (inactivo).
            self::AUTH_USER_INACTIVE => 403,

            // 404 Not Found: Recurso no encontrado.
            self::AUTH_USER_NOT_FOUND => 404,

            // 409 Conflict: Cuando intentas crear algo que ya existe.
            self::AUTH_USER_ALREADY_EXISTS => 409,

            // 423 Locked: Recurso bloqueado.
            self::RESOURCE_LOCKED => 423,
        };
    }
}
