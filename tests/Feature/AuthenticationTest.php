<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    public function testUserRegistrationSuccess(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.new@example.com',
            'password' => 'password123',
            'phone_number' => '+1234567890'
        ];

        $request = $this->createRequest('POST', '/register', $userData);
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 201);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuario registrado exitosamente', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('token', $body);
        $this->assertEquals('john.new@example.com', $body['user']['email']);
        $this->assertEquals('John Doe', $body['user']['full_name']);
    }

    public function testUserRegistrationEmailExists(): void
    {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com', // Already exists from setup
            'password' => 'password123',
            'phone_number' => '+1234567890'
        ];

        $request = $this->createRequest('POST', '/register', $userData);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 409, 'El correo electrónico ya existe');
    }

    public function testUserRegistrationValidationError(): void
    {
        $userData = [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'phone_number' => 'invalid'
        ];

        $request = $this->createRequest('POST', '/register', $userData);
        $response = $this->app->handle($request);

        $this->assertValidationErrorResponse($response);
    }

    public function testUserLoginSuccess(): void
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $request = $this->createRequest('POST', '/login', $loginData);
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Inicio de sesión exitoso', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('token', $body);
        $this->assertEquals('test@example.com', $body['user']['email']);
    }

    public function testUserLoginInvalidCredentials(): void
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $request = $this->createRequest('POST', '/login', $loginData);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 401, 'Credenciales inválidas');
    }

    public function testUserLoginValidationError(): void
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => ''
        ];

        $request = $this->createRequest('POST', '/login', $loginData);
        $response = $this->app->handle($request);

        $this->assertValidationErrorResponse($response);
    }
}