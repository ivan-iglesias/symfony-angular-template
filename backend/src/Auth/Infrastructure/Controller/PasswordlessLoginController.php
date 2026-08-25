<?php

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Actions\PasswordlessLoginAction;
use App\Auth\Application\DTO\PasswordlessLoginInput;
use App\Shared\Infrastructure\Controller\BaseApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordlessLoginController extends BaseApiController
{
    public function __construct(
        private readonly PasswordlessLoginAction $action,
        LoggerInterface $logger,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $validator);
    }

    #[Route('/api/auth/login-code', name: 'api_passwordless_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login-code',
        summary: 'Solicita un código de acceso de 5 dígitos vía email',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            description: 'Email del usuario para recibir el código OTP',
            content: new OA\JsonContent(ref: new Model(type: PasswordlessLoginInput::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Solicitud procesada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Si el email existe...')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object', example: ['email' => 'Email inválido'])
                    ]
                )
            )
        ]
    )]
    #[Idempotent]
    public function __invoke(Request $request): JsonResponse
    {
        return $this->handleInput(
            $request,
            PasswordlessLoginInput::class,
            fn($input) => $this->action->execute($input)
        );
    }
}
