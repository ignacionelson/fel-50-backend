<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private static $secretKey;
    private static $algorithm;
    private static $expiry;

    public static function init()
    {
        self::$secretKey = $_ENV['JWT_SECRET'] ?? 'default-secret-key';
        self::$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        self::$expiry = $_ENV['JWT_EXPIRY'] ?? 3600;
    }

    public static function generateToken($userId)
    {
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + self::$expiry
        ];

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}