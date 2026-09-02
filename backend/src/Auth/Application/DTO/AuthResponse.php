<?php

namespace App\Auth\Application\DTO;

use App\Auth\Domain\Entity\User;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class AuthResponse
{
    public function __construct(
        #[SerializedName('access_token')]
        public readonly string $token,
        public readonly string $email,
        public readonly array $roles,

        #[Ignore]
        public readonly string $refreshToken
    ) {}

    public static function create(string $token, string $refreshToken, User $user): self
    {
        return new self(
            token: $token,
            email: $user->getEmail(),
            roles: $user->getRoles(),
            refreshToken: $refreshToken
        );
    }
}
