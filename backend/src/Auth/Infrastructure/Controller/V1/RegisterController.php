<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\RegisterAction;
use App\Auth\Application\DTO\RegisterInput;
use App\Shared\Infrastructure\Response\ApiResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterAction $action,
    ) {}

    #[Route('/api/v1/auth/register', name: 'api_auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: 'Registra un nuevo usuario en la plataforma',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: RegisterInput::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario creado con éxito. Se ha enviado un email de confirmación.'
            ),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 400, description: 'El email ya está registrado')
        ]
    )]
    #[Idempotent]
    public function __invoke(Request $request, RegisterInput $input): ApiResponse
    {
        $responseDto = $this->action->execute($input);

        return ApiResponse::success($responseDto);
    }
}
