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

    #[Route('/api/v1/auth/login', name: 'api_login', methods: ['POST'])]
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
                description: 'Autenticación exitosa. Retorna el access_token en el body y setea la cookie HTTP-Only con el refresh_token',
                headers: [
                    new OA\Header(
                        header: 'Set-Cookie',
                        description: 'Cookie HTTP-Only',
                        schema: new OA\Schema(type: 'string', example: 'REFRESH_TOKEN=2d5c8b7f74...; Path=/api/v1/auth; Secure; HttpOnly; SameSite=Strict')
                    )
                ],
                content: new OA\JsonContent(ref: new Model(type: AuthResponse::class))
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales inválidas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Credenciales inválidas')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación en los datos de entrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: ['email' => 'El formato del email no es válido.']
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error crítico del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Ha ocurrido un error inesperado.')
                    ]
                )
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
