<?php

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Actions\LoginAction;
use App\Auth\Application\DTO\AuthResponse;
use App\Auth\Application\DTO\LoginInput;
use App\Shared\Infrastructure\Response\ApiResponse;
use App\Shared\Infrastructure\Security\RateLimiter\HasRateLimiterTrait;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    use HasRateLimiterTrait;

    public function __construct(
        private readonly LoginAction $action
    ) {}

    #[Route('/api/auth/login', name: 'api_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Inicia sesión para obtener el token JWT',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            description: 'Credenciales de acceso',
            content: new OA\JsonContent(ref: new Model(type: LoginInput::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autenticación exitosa',
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
        $this->checkRateLimit($request);

        $responseDto = $this->action->execute($input);

        return ApiResponse::success($responseDto);
    }
}
