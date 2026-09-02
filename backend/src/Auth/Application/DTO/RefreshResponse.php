<?php

namespace App\Auth\Application\DTO;

use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RefreshResponse
{
    public function __construct(
        #[SerializedName('access_token')]
        public string $accessToken,

        // -------------------------------------------------------------
        // Re-autentica a menos que pasen meses sin abrir la app o le de a Logout.
        // -------------------------------------------------------------

        // #[Ignore]
        // public string $newRefreshToken
    ) {}
}
