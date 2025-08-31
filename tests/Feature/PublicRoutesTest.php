<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function testHealthCheck(): void
    {
        $request = $this->createRequest('GET', '/health');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('status', $body);
        $this->assertEquals('healthy', $body['status']);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('timestamp', $body);
        $this->assertArrayHasKey('service', $body);
        $this->assertArrayHasKey('version', $body);
    }

    public function testPublicEndpoint(): void
    {
        $request = $this->createRequest('GET', '/public');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Esta es una ruta pública - no requiere autenticación', $body['message']);
        $this->assertArrayHasKey('timestamp', $body);
        $this->assertArrayHasKey('data', $body);
        $this->assertEquals('1.0', $body['data']['api_version']);
        $this->assertEquals('active', $body['data']['status']);
    }
}