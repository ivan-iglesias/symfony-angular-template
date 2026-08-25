<?php

namespace App\Tests\Unit\Auth\Infrastructure\Controller;

use App\Auth\Application\Actions\LoginAction;
use App\Auth\Domain\Service\AuthServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginActionTest extends TestCase
{
    public function testInvokeReturnsTokenOnSuccess(): void
    {
        $authServiceMock = $this->createMock(AuthServiceInterface::class);

        $authServiceMock->expects($this->once())
            ->method('authenticate')
            ->with('admin@acme.com', 'password123')
            ->willReturn('fake_token_jwt_123');

        $controller = new LoginAction($authServiceMock);

        // 3. SIMULAMOS LA PETICIÓN (Request)
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'admin@acme.com',
            'password' => 'password123'
        ]));

        // 4. EJECUTAMOS
        $response = $controller->__invoke($request);

        // 5. ASERCIONES
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('fake_token_jwt_123', $responseData['token']);
    }
}
