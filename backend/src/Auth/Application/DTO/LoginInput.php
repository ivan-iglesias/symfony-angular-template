<?php

namespace App\Auth\Application\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'El email no puede estar vacío.')]
        #[Assert\Email(message: 'El formato del email no es válido.')]
        #[OA\Property(description: 'Email del usuario', example: 'admin@acme.com')]
        public string $email = '',

        #[Assert\NotBlank(message: 'La contraseña es obligatoria.')]
        #[Assert\Length(min: 6, minMessage: 'La contraseña debe tener al menos 6 caracteres.')]
        #[OA\Property(description: 'Contraseña del usuario', example: 'admin123')]
        public string $password = ''
    ) {}
}
