<?php

namespace App\Auth\Domain\Service;

use Symfony\Component\Security\Core\User\UserInterface;

interface RefreshTokenGeneratorInterface
{
    /**
     * Genera y persiste un refresh token para el usuario especificado.
     * Devuelve el string del token generado.
     */
    public function createForUser(UserInterface $user): string;

    /**
     * Verificar si el token existe en Redis.
     * Retornar el email/identificador del usuario o null si no existe/expiró.
     */
    public function validateAndGetUser(string $refreshToken): ?string;


    /**
     * Revoca todos los refresh tokens de un usuario.
     */
    public function revokeAllForUser(UserInterface $user): void;
}
