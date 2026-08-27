<?php

namespace App\Shared\Domain\Exception;

enum ApiErrorCode: string
{
    // Errores de Entrada / Infraestructura HTTP
    case INVALID_JSON = 'INVALID_JSON';
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    case ACCESS_DENIED = 'ACCESS_DENIED';
    case TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    case HTTP_ERROR = 'HTTP_ERROR';

    // Autenticación y Autorización
    case AUTH_INVALID_CODE = 'AUTH_INVALID_CODE';
    case AUTH_INVALID_TOKEN = 'AUTH_INVALID_TOKEN';
    case AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    case AUTH_USER_NOT_FOUND = 'AUTH_USER_NOT_FOUND';
    case AUTH_USER_ALREADY_EXISTS = 'AUTH_USER_ALREADY_EXISTS';
    case AUTH_USER_INACTIVE = 'AUTH_USER_INACTIVE';

    // Idempotencia y Concurrencia
    case RESOURCE_LOCKED = 'RESOURCE_LOCKED';
    case IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD = 'IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD';
    case IDEMPOTENCY_IN_PROGRESS = 'IDEMPOTENCY_IN_PROGRESS';

    public function defaultMessage(): string
    {
        return match ($this) {
            self::INVALID_JSON => 'El cuerpo de la petición contiene un JSON inválido o mal formado.',
            self::VALIDATION_ERROR => 'Error de validación en los campos de la petición.',
            self::RESOURCE_NOT_FOUND => 'El recurso solicitado no existe.',
            self::METHOD_NOT_ALLOWED => 'Método HTTP no permitido para esta ruta.',
            self::ACCESS_DENIED => 'Acceso denegado al recurso solicitado.',
            self::TOO_MANY_REQUESTS => 'Has superado el límite de peticiones permitidas por minuto.',
            self::HTTP_ERROR => 'Ha ocurrido un error en la petición HTTP.',

            self::AUTH_INVALID_CODE => 'El código es incorrecto o ha caducado.',
            self::AUTH_INVALID_TOKEN => 'El token proporcionado no existe o ha expirado.',
            self::AUTH_INVALID_CREDENTIALS => 'Credenciales incorrectas.',
            self::AUTH_USER_NOT_FOUND => 'Usuario no encontrado.',
            self::AUTH_USER_ALREADY_EXISTS => 'Email ya registrado en el sistema.',
            self::AUTH_USER_INACTIVE => 'La cuenta de usuario no está activa.',

            self::RESOURCE_LOCKED => 'La solicitud ya se está procesando en otra sesión.',
            self::IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD => 'La Idempotency-Key ya fue usada con un payload diferente.',
            self::IDEMPOTENCY_IN_PROGRESS => 'La petición con esta Idempotency-Key ya está en proceso.',
        };
    }

    public function httpCode(): int
    {
        return match ($this) {
            // 400 Bad Request
            self::HTTP_ERROR,
            self::INVALID_JSON => 400,

            // 401 Unauthorized: Cuando las credenciales (código o token) fallan.
            self::AUTH_INVALID_CODE,
            self::AUTH_INVALID_TOKEN,
            self::AUTH_INVALID_CREDENTIALS => 401,

            // 403 Forbidden: El usuario existe pero tiene el paso prohibido (inactivo).
            self::ACCESS_DENIED,
            self::AUTH_USER_INACTIVE => 403,

            // 404 Not Found: Recurso no encontrado.
            self::RESOURCE_NOT_FOUND,
            self::AUTH_USER_NOT_FOUND => 404,

            // 405 Method Not Allowed
            self::METHOD_NOT_ALLOWED => 405,

            // 409 Conflict
            self::AUTH_USER_ALREADY_EXISTS,
            self::IDEMPOTENCY_IN_PROGRESS => 409,

            // 422 Unprocessable Content
            self::VALIDATION_ERROR,
            self::IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD => 422,

            // 423 Locked
            self::RESOURCE_LOCKED => 423,

            // 429 Too Many Requests
            self::TOO_MANY_REQUESTS => 429,
        };
    }
}
