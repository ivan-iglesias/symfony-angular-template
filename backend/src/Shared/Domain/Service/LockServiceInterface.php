<?php

namespace App\Shared\Domain\Service;

interface LockServiceInterface
{
    /**
     * Intenta ejecutar una callable bajo un bloqueo exclusivo.
     * Retorna true si se ejecutó con éxito, o false si el recurso estaba bloqueado.
     */
    public function acquireAndExecute(string $resourceKey, callable $action, float $ttl = 10.0): bool;
}
