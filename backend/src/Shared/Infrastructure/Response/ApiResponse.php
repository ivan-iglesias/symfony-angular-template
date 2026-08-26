<?php

namespace App\Shared\Infrastructure\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    public function __construct(
        string $code,
        string $message,
        mixed $data = null,
        int $status = 200,
    ) {
        parent::__construct([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            // 'correlation_id' => null
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Recurso creado correctamente'): self
    {
        return new self('SUCCESS', $message, $data, 201);
    }

    public static function success(mixed $data = null, string $message = 'Operación realizada correctamente'): self
    {
        return new self('SUCCESS', $message, $data, 200);
    }

    public static function error(string $code, string $message, int $status = 400, mixed $data = null): self
    {
        return new self($code, $message, $data, $status);
    }

    public static function critical(string $message = 'Error interno del servidor'): self
    {
        return new self('INTERNAL_SERVER_ERROR', $message, null, 500);
    }
}
