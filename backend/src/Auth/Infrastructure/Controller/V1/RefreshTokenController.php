<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\RefreshTokenAction;
use App\Auth\Application\DTO\RefreshResponse;
use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use App\Shared\Infrastructure\Response\ApiResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenAction $action,
        private readonly SerializerInterface $serializer,
        private readonly string $cookieName = AuthCookieFactoryInterface::REFRESH_TOKEN_COOKIE_NAME
    ) {}

    #[Route('/api/v1/auth/refresh', name: 'api_v1_auth_refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/refresh',
        summary: 'Renueva el JWT access_token leyendo la cookie HTTP-Only existente',
        description: 'Requiere que la cookie HTTP-Only `refresh_token` sea enviada automáticamente en las cabeceras de la petición por el navegador o cliente.',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'SUCCESS - Token JWT renovado con éxito. Mantiene la cookie HTTP-Only previa sin alterarla.'
            ),
            new OA\Response(
                response: 401,
                description: 'AUTH_REFRESH_TOKEN_MISSING / AUTH_REFRESH_TOKEN_INVALID - El refresh token es inválido o ha expirado.'
            ),
            new OA\Response(
                response: 404,
                description: 'AUTH_USER_NOT_FOUND - Usuario no encontrado.'
            ),
        ]
    )]
    public function __invoke(Request $request): ApiResponse
    {
        $refreshToken = $request->cookies->get($this->cookieName);

        $responseDto = $this->action->execute($refreshToken);

        $normalizedData = $this->serializer->normalize($responseDto);

        $response = ApiResponse::success($normalizedData);

        // -------------------------------------------------------------
        // Re-autentica a menos que pasen meses sin abrir la app o le de a Logout.
        // -------------------------------------------------------------

        // $cookie = $this->cookieFactory->createRefreshTokenCookie($responseDto->newRefreshToken);

        // $response->headers->setCookie($cookie);

        return $response;
    }
}
