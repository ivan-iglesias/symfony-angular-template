<?php

namespace App\Auth\Application\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordlessLoginInput
{
    public function __construct(
        #[Assert\NotBlank(message: "El email no puede estar vacío.")]
        #[Assert\Email(message: "El formato del email no es válido.")]
        #[OA\Property(description: 'Email del usuario', example: 'admin@acme.com')]
        public string $email = ''
    ) {}
}
