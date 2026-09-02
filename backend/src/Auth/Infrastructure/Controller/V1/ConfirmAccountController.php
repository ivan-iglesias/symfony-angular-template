<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\ConfirmAction;
use App\Shared\Infrastructure\Response\ApiResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class ConfirmAccountController extends AbstractController
{
    public function __construct(
        private readonly ConfirmAction $confirmAction,
    ) {}

    #[Route('/api/v1/auth/confirm/{token}', name: 'api_auth_confirm', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/auth/confirm/{token}',
        summary: 'Confirma la cuenta de un usuario mediante un token de email',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'token',
                in: 'path',
                description: 'El token recibido por email',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cuenta activada correctamente'
            ),
            new OA\Response(
                response: 404,
                description: 'Token inválido o expirado'
            )
        ]
    )]
    public function __invoke(string $token): ApiResponse
    {
        $this->confirmAction->execute($token);

        return ApiResponse::success(
            message: '¡Cuenta confirmada con éxito! Ya puedes iniciar sesión.'
        );
    }
}
