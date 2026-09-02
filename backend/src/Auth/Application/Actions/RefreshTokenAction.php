<?php

namespace App\Auth\Application\Actions;

use App\Auth\Application\DTO\RefreshResponse;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\RefreshTokenGeneratorInterface;
use App\Shared\Domain\Exception\ApiErrorCode;
use App\Shared\Domain\Exception\BusinessException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final readonly class RefreshTokenAction
{
    public function __construct(
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private UserRepositoryInterface $userRepository,
        private JWTTokenManagerInterface $jwtManager,
    ) {}

    public function execute(?string $currentRefreshToken): RefreshResponse
    {
        if (!$currentRefreshToken) {
            throw new BusinessException(ApiErrorCode::AUTH_REFRESH_TOKEN_MISSING);
        }

        // Validar y obtener el identificador del usuario desde Redis
        $userIdentifier = $this->refreshTokenGenerator->validateAndGetUser($currentRefreshToken);

        if (!$userIdentifier) {
            throw new BusinessException(ApiErrorCode::AUTH_REFRESH_TOKEN_INVALID);
        }

        $user = $this->userRepository->findByEmail($userIdentifier);

        if (!$user) {
            throw new BusinessException(ApiErrorCode::AUTH_USER_NOT_FOUND);
        }

        // -------------------------------------------------------------
        // Re-autenticar al usuario cada X días sin excepción.
        // -------------------------------------------------------------

        $newAccessToken = $this->jwtManager->create($user);

        return new RefreshResponse(
            accessToken: $newAccessToken
        );

        // -------------------------------------------------------------
        // Re-autentica a menos que pasen meses sin abrir la app o le de a Logout.
        // -------------------------------------------------------------

        // $this->refreshTokenGenerator->revokeToken($currentRefreshToken);

        // $newRefreshToken = $this->refreshTokenGenerator->createForUser($user);

        // return new RefreshResponse(
        //     accessToken: $newAccessToken,
        //     newRefreshToken: $newRefreshToken
        // );
    }
}
