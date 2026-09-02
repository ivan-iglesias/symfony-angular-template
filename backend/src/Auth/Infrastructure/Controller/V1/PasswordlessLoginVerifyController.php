<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\PasswordlessLoginVerifyAction;
use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\PasswordlessLoginVerifyInput;
use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use App\Shared\Infrastructure\Response\ApiResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class PasswordlessLoginVerifyController extends AbstractController
{
    public function __construct(
        private readonly PasswordlessLoginVerifyAction $action,
        private readonly AuthCookieFactoryInterface $cookieFactory,
        private readonly SerializerInterface $serializer
    ) { }

    #[Route('/api/v1/auth/login-code/verify', name: 'api_v1_passwordless_login_verify', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/login-code/verify',
        summary: 'Verifica el código de acceso y devuelve el token JWT',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            description: 'Credenciales de acceso (Email + Código de 5 dígitos)',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PasswordlessLoginVerifyInput::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'SUCCESS - Autenticación exitosa. "access_token" en el body y setea la cookie HTTP-Only con el "refresh_token"'
            ),
            new OA\Response(
                response: 401,
                description: 'AUTH_INVALID_CODE - Código de validación incorrecto o caducado'
            ),
            new OA\Response(
                response: 404,
                description: 'AUTH_USER_NOT_FOUND - Usuario no encontrado.'
            ),
        ]
    )]
    public function __invoke(Request $request, PasswordlessLoginVerifyInput $input): ApiResponse
    {
        $responseDto = $this->action->execute($input);

        $normalizedData = $this->serializer->normalize($responseDto);

        $response = ApiResponse::success($normalizedData);

        $cookie = $this->cookieFactory->createRefreshTokenCookie(
            $responseDto->refreshToken
        );

        $response->headers->setCookie($cookie);

        return $response;
    }
}
