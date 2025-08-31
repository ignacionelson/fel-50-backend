<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\JWTService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTServiceTest extends TestCase
{
    public function testTokenGeneration(): void
    {
        $userId = 123;
        $token = JWTService::generateToken($userId);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Basic JWT structure check (3 parts separated by dots)
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testTokenValidation(): void
    {
        $userId = 456;
        $token = JWTService::generateToken($userId);

        $decodedUserId = JWTService::validateToken($token);
        
        $this->assertEquals($userId, $decodedUserId);
    }

    public function testInvalidTokenValidation(): void
    {
        $invalidToken = 'invalid.token.here';
        
        $result = JWTService::validateToken($invalidToken);
        
        $this->assertNull($result);
    }

    public function testExpiredTokenValidation(): void
    {
        // Create a token that expires immediately
        $payload = [
            'user_id' => 789,
            'iat' => time(),
            'exp' => time() - 1 // Already expired
        ];

        $expiredToken = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        
        $result = JWTService::validateToken($expiredToken);
        
        $this->assertNull($result);
    }

    public function testTokenWithDifferentUserIds(): void
    {
        $userId1 = 111;
        $userId2 = 222;
        
        $token1 = JWTService::generateToken($userId1);
        $token2 = JWTService::generateToken($userId2);

        $this->assertNotEquals($token1, $token2);
        
        $decodedUserId1 = JWTService::validateToken($token1);
        $decodedUserId2 = JWTService::validateToken($token2);
        
        $this->assertEquals($userId1, $decodedUserId1);
        $this->assertEquals($userId2, $decodedUserId2);
    }
}