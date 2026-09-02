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
}
