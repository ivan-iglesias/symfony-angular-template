<?php

namespace App\Tests\Application\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLoginReturnsJwtToken(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'admin@acme.com',
                'password' => 'admin123'
            ])
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $data);
    }

    public function testInvalidCredentialsReturnError(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'admin@acme.com', 'password' => 'wrong-password'])
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
