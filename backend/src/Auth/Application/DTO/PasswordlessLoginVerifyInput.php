<?php

namespace App\Auth\Application\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordlessLoginVerifyInput
{
    #[Assert\NotBlank(message: "El email es obligatorio")]
    #[Assert\Email(message: "El formato del email no es válido")]
    #[OA\Property(description: 'Email del usuario', example: 'admin@acme.com')]
    public string $email;

    #[Assert\NotBlank(message: "El código es obligatorio")]
    #[Assert\Length(min: 5, max: 5, exactMessage: "El código debe tener exactamente {{ limit }} dígitos")]
    #[Assert\Regex(pattern: "/^[0-9]+$/", message: "El código solo debe contener números")]
    public string $code;

    public function __construct(array $data)
    {
        $this->email = $data['email'] ?? '';
        $this->code = $data['code'] ?? '';
    }
}
