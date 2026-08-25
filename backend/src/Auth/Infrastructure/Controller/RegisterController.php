<?php

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Actions\RegisterAction;
use App\Auth\Application\DTO\RegisterInput;
use App\Shared\Infrastructure\Controller\BaseApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends BaseApiController
{
    public function __construct(
        private readonly RegisterAction $action,
        LoggerInterface $logger,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $validator);
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/register',
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
    public function __invoke(Request $request): JsonResponse
    {
        return $this->handleInput(
            $request,
            RegisterInput::class,
            fn($input) => $this->action->execute($input)
        );
    }
}
