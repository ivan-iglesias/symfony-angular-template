<?php

namespace App\Auth\Application\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[OA\Property(description: 'Nombre del usuario', example: 'John')]
        public string $name = '',

        #[Assert\NotBlank]
        #[OA\Property(description: 'Apellido del usuario', example: 'Doe')]
        public string $lastName = '',

        #[Assert\NotBlank]
        #[Assert\Email]
        #[OA\Property(description: 'Email del usuario', example: 'john@doe.com')]
        public string $email = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        #[OA\Property(description: 'Pass del usuario', example: 'secret')]
        public string $password = '',
     ) {}
}
