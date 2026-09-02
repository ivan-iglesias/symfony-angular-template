<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\LoginAction;
use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\LoginInput;
use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use App\Shared\Infrastructure\Response\ApiResponse;
use App\Shared\Infrastructure\Security\RateLimiter\RateLimiterService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly LoginAction $action,
        private readonly RateLimiterService $rateLimiter,
        private readonly AuthCookieFactoryInterface $cookieFactory,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/api/v1/auth/login', name: 'api_v1_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Inicia sesión para obtener el token JWT',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            description: 'Credenciales de acceso',
            content: new OA\JsonContent(ref: new Model(type: LoginInput::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'SUCCESS - Autenticación exitosa. "access_token" en el body y setea la cookie HTTP-Only con el "refresh_token"'
            ),
            new OA\Response(
                response: 401,
                description: 'AUTH_INVALID_CREDENTIALS - Credenciales inválidas',
            ),
            new OA\Response(
                response: 422,
                description: 'AUTH_USER_INACTIVE - La cuenta de usuario no está activa',
            )
        ]
    )]
    public function __invoke(Request $request, LoginInput $input): ApiResponse
    {
        $this->rateLimiter->check($request);

        $responseDto = $this->action->execute($input);

        $normalizedData = $this->serializer->normalize($responseDto);

        $response = ApiResponse::success($normalizedData);

        $cookie = $this->cookieFactory->createRefreshTokenCookie($responseDto->refreshToken);

        $response->headers->setCookie($cookie);

        return $response;
    }
}
