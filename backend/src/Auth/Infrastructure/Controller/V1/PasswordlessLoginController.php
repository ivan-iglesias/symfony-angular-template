<?php

namespace App\Auth\Infrastructure\Controller\V1;

use App\Auth\Application\Actions\PasswordlessLoginAction;
use App\Auth\Application\DTO\PasswordlessLoginInput;
use App\Shared\Infrastructure\Response\ApiResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordlessLoginController extends AbstractController
{
    public function __construct(
        private readonly PasswordlessLoginAction $action,
    ) { }

    #[Route('/api/v1/auth/login-code', name: 'api_v1_passwordless_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/login-code',
        summary: 'Solicita un código de acceso de 5 dígitos vía email',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            description: 'Email del usuario para recibir el código OTP',
            content: new OA\JsonContent(ref: new Model(type: PasswordlessLoginInput::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'SUCCESS - Solicitud procesada, independientemente de si el usuario existe o no.'
            )
        ]
    )]
    #[Idempotent]
    public function __invoke(Request $request, PasswordlessLoginInput $input): ApiResponse
    {
        $responseDto = $this->action->execute($input);

        return ApiResponse::success($responseDto);
    }
}
