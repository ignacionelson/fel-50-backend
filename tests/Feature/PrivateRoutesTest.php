<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivateRoutesTest extends TestCase
{
    public function testUserProfileWithValidToken(): void
    {
        $request = $this->createAuthenticatedRequest('GET', '/private/profile');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Esta es una ruta privada - autenticación requerida', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('timestamp', $body);
        $this->assertEquals($this->testUser->id, $body['user']['id']);
        $this->assertEquals($this->testUser->email, $body['user']['email']);
    }

    public function testUserProfileWithoutToken(): void
    {
        $request = $this->createRequest('GET', '/private/profile');
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 401, 'Authorization header missing');
    }

    public function testUserProfileWithInvalidToken(): void
    {
        $request = $this->createRequest('GET', '/private/profile', [], [
            'Authorization' => 'Bearer invalid_token_here'
        ]);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 401, 'Invalid or expired token');
    }

    public function testUserProfileWithInvalidAuthFormat(): void
    {
        $request = $this->createRequest('GET', '/private/profile', [], [
            'Authorization' => 'InvalidFormat token_here'
        ]);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 401, 'Invalid authorization format');
    }
}