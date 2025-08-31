<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Database\Capsule\Manager as DB;

abstract class TestCase extends BaseTestCase
{
    protected App $app;
    protected ?User $testUser = null;
    protected ?User $testAdminUser = null;
    protected ?string $testToken = null;
    protected ?string $testAdminToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Slim app
        $this->app = AppFactory::create();
        
        // Setup database tables
        $this->setupDatabase();
        
        // Create test users
        $this->createTestUsers();
        
        // Include routes
        require __DIR__ . '/../src/Routes/routes.php';
    }

    protected function tearDown(): void
    {
        // Clean database
        $this->cleanDatabase();
        
        parent::tearDown();
    }

    protected function setupDatabase(): void
    {
        // Create users table
        DB::schema()->create('users', function ($table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->json('roles')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function cleanDatabase(): void
    {
        DB::schema()->dropIfExists('users');
    }

    protected function createTestUsers(): void
    {
        // Regular test user
        $this->testUser = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone_number' => '+1234567890',
            'roles' => ['visitante']
        ]);

        // Admin test user  
        $this->testAdminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'phone_number' => '+1234567891',
            'roles' => ['admin']
        ]);

        // Generate tokens
        $this->testToken = JWTService::generateToken($this->testUser->id);
        $this->testAdminToken = JWTService::generateToken($this->testAdminUser->id);
    }

    protected function createRequest(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): ServerRequestInterface {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri);
        
        // Add headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        
        // Add JSON body for POST/PUT requests
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $request->getBody()->write(json_encode($data));
            $request->getBody()->rewind();
        }
        
        return $request;
    }

    protected function createAuthenticatedRequest(
        string $method,
        string $uri,
        array $data = [],
        string $token = null
    ): ServerRequestInterface {
        $token = $token ?: $this->testToken;
        
        return $this->createRequest($method, $uri, $data, [
            'Authorization' => 'Bearer ' . $token
        ]);
    }

    protected function createAdminRequest(
        string $method,
        string $uri,
        array $data = []
    ): ServerRequestInterface {
        return $this->createRequest($method, $uri, $data, [
            'Authorization' => 'Bearer ' . $this->testAdminToken
        ]);
    }

    protected function getResponseBody($response): array
    {
        $response->getBody()->rewind();
        return json_decode($response->getBody()->getContents(), true);
    }

    protected function assertSuccessResponse($response, int $statusCode = 200): array
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        
        $body = $this->getResponseBody($response);
        $this->assertIsArray($body);
        
        return $body;
    }

    protected function assertErrorResponse($response, int $statusCode, string $errorMessage = null): array
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        
        $body = $this->getResponseBody($response);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        
        if ($errorMessage) {
            $this->assertEquals($errorMessage, $body['error']);
        }
        
        return $body;
    }

    protected function assertValidationErrorResponse($response): array
    {
        $this->assertEquals(422, $response->getStatusCode());
        
        $body = $this->getResponseBody($response);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('errors', $body);
        
        return $body;
    }
}