<?php

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Actions\LogoutAction;
use App\Auth\Domain\Service\AuthCookieFactoryInterface;
use App\Shared\Infrastructure\Response\ApiResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends AbstractController
{
    public function __construct(
        private readonly LogoutAction $action,
        private readonly AuthCookieFactoryInterface $cookieFactory,
        private readonly string $cookieName = AuthCookieFactoryInterface::REFRESH_TOKEN_COOKIE_NAME
    ) {}

    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Cierra la sesión destruyendo el Refresh Token en Redis y expirando la cookie HTTP-Only',
        description: 'Requiere que la cookie HTTP-Only `refresh_token` sea enviada automáticamente en las cabeceras de la petición por el navegador o cliente.',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada correctamente y cookie eliminada del navegador.'
            )
        ]
    )]
    public function __invoke(Request $request): ApiResponse
    {
        $refreshToken = $request->cookies->get($this->cookieName);

        $this->action->execute($refreshToken);

        $response = ApiResponse::success(['message' => 'Sesión cerrada con éxito.']);

        // Inyecta la cookie con valor vacío y expiración en el pasado para que el navegador la borre
        $response->headers->setCookie($this->cookieFactory->createLogoutCookie());

        return $response;
    }
}
