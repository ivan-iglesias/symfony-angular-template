<?php

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\Enum\SecurityTokenType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'security_tokens')]
#[ORM\Index(columns: ['token'], name: 'idx_token_lookup')]
class SecurityToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'securityTokens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 100, unique: true)]
    private string $token;

    #[ORM\Column(length: 20, type: 'string', enumType: SecurityTokenType::class)]
    private SecurityTokenType $type;

    #[ORM\Column]
    public \DateTimeImmutable $expiresAt;

    public function __construct(User $user, string $token, SecurityTokenType $type, int $ttlInMinutes = 24 * 60)
    {
        $this->id = Uuid::v7();
        $this->user = $user;
        $this->token = $token;
        $this->type = $type;
        $this->expiresAt = new \DateTimeImmutable("+{$ttlInMinutes} minutes");
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function isValid(): bool
    {
        return $this->expiresAt > new \DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): SecurityTokenType
    {
        return $this->type;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
