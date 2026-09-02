<?php

namespace App\Shared\Domain\Service;

use Symfony\Component\HttpFoundation\Response;

interface IdempotencyServiceInterface
{
    /**
     * Busca una respuesta previamente almacenada asociada a la clave de idempotencia.
     *
     * @param string $key Clave única de idempotencia enviada por el cliente.
     * @param string $fingerprint Hash/huella del contenido de la petición para verificar integridad.
     *
     * @return Response|null Devuelve la respuesta cacheada (marcada como 'replayed'),
     *                       una respuesta de error si el payload cambió para la misma clave,
     *                       o null si la clave no existe en caché.
     */
    public function getSavedResponse(string $key, string $fingerprint): ?Response;

    /**
     * Guarda la respuesta de una petición para futuras ejecuciones idempotentes.
     *
     * @param string $key Clave única de idempotencia enviada por el cliente.
     * @param string $fingerprint Hash/huella del contenido de la petición procesada.
     * @param Response $response Respuesta HTTP generada que se desea cachear.
     * @param int $ttl Tiempo de vida en segundos durante el cual se preservará la respuesta.
     */
    public function saveResponse(string $key, string $fingerprint, Response $response, int $ttl): void;
}
