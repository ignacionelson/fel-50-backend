<?php

use Psr\Http\Message\ResponseInterface as Response;

if (!function_exists('response')) {
    /**
     * Create a JSON response
     */
    function response(): Response
    {
        global $app;
        return $app->getResponseFactory()->createResponse();
    }
}

if (!function_exists('jsonResponse')) {
    /**
     * Create a JSON response with data
     */
    function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}

if (!function_exists('successResponse')) {
    /**
     * Create a success JSON response
     */
    function successResponse(Response $response, $data = null, string $message = 'Operación exitosa', int $status = 200): Response
    {
        $responseData = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $responseData['data'] = $data;
        }
        
        return jsonResponse($response, $responseData, $status);
    }
}

if (!function_exists('errorResponse')) {
    /**
     * Create an error JSON response
     */
    function errorResponse(Response $response, string $message = 'Error al procesar solicitud', int $status = 400, array $errors = []): Response
    {
        $responseData = [
            'success' => false,
            'error' => $message
        ];
        
        if (!empty($errors)) {
            $responseData['errors'] = $errors;
        }
        
        return jsonResponse($response, $responseData, $status);
    }
}

if (!function_exists('validationErrorResponse')) {
    /**
     * Create a validation error JSON response
     */
    function validationErrorResponse(Response $response, array $errors): Response
    {
        return jsonResponse($response, [
            'success' => false,
            'error' => 'Error de validación',
            'errors' => $errors
        ], 422);
    }
}

if (!function_exists('generateUuid')) {
    /**
     * Generate a UUID v4
     */
    function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Sanitize user input
     */
    function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map('sanitizeInput', $input);
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validateEmail')) {
    /**
     * Validate email format
     */
    function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validatePhone')) {
    /**
     * Validate phone number (Argentina format)
     */
    function validatePhone(string $phone): bool
    {
        // Remove spaces, dashes, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Check for Argentine phone format
        return preg_match('/^(?:\+?54)?(?:9)?[1-9]\d{9,11}$/', $phone);
    }
}

if (!function_exists('validateCUIT')) {
    /**
     * Validate CUIT/CUIL (Argentina tax ID)
     */
    function validateCUIT(string $cuit): bool
    {
        // Remove dashes
        $cuit = str_replace('-', '', $cuit);
        
        if (!preg_match('/^\d{11}$/', $cuit)) {
            return false;
        }
        
        $base = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $aux = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $aux += $cuit[$i] * $base[$i];
        }
        
        $aux = 11 - ($aux % 11);
        
        if ($aux == 11) {
            $aux = 0;
        } elseif ($aux == 10) {
            $aux = 9;
        }
        
        return $aux == $cuit[10];
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format number as currency (Argentine Peso)
     */
    function formatCurrency(float $amount, string $symbol = '$'): string
    {
        return $symbol . ' ' . number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date to Spanish format
     */
    function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime to Spanish format
     */
    function formatDateTime($datetime, string $format = 'd/m/Y H:i:s'): string
    {
        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }
        
        return $datetime->format($format);
    }
}

if (!function_exists('slugify')) {
    /**
     * Convert string to URL-friendly slug
     */
    function slugify(string $text): string
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        return $text ?: 'n-a';
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate text to specified length
     */
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
    }
}

if (!function_exists('getClientIp')) {
    /**
     * Get client IP address
     */
    function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return '0.0.0.0';
    }
}

if (!function_exists('randomString')) {
    /**
     * Generate random string
     */
    function randomString(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('arrayGet')) {
    /**
     * Get value from array using dot notation
     */
    function arrayGet(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            
            $array = $array[$segment];
        }
        
        return $array;
    }
}

if (!function_exists('arraySet')) {
    /**
     * Set array value using dot notation
     */
    function arraySet(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            
            $array = &$array[$key];
        }
        
        $array[array_shift($keys)] = $value;
    }
}

if (!function_exists('arrayOnly')) {
    /**
     * Get only specified keys from array
     */
    function arrayOnly(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }
}

if (!function_exists('arrayExcept')) {
    /**
     * Get all except specified keys from array
     */
    function arrayExcept(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }
}

if (!function_exists('calculatePercentage')) {
    /**
     * Calculate percentage
     */
    function calculatePercentage(float $value, float $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }
        
        return round(($value / $total) * 100, $decimals);
    }
}

if (!function_exists('calculateDiscount')) {
    /**
     * Calculate discount amount
     */
    function calculateDiscount(float $price, float $discountPercentage): float
    {
        return $price * ($discountPercentage / 100);
    }
}

if (!function_exists('calculateTax')) {
    /**
     * Calculate tax amount (default IVA 21% Argentina)
     */
    function calculateTax(float $amount, float $taxRate = 21): float
    {
        return $amount * ($taxRate / 100);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable value
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }
        
        return $value;
    }
}

if (!function_exists('logActivity')) {
    /**
     * Log activity to file
     */
    function logActivity(string $action, array $data = []): void
    {
        $logFile = __DIR__ . '/../../logs/activity.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'ip' => getClientIp(),
            'data' => $data
        ];
        
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('isProduction')) {
    /**
     * Check if application is in production environment
     */
    function isProduction(): bool
    {
        return env('APP_ENV', 'production') === 'production';
    }
}

if (!function_exists('isDevelopment')) {
    /**
     * Check if application is in development environment
     */
    function isDevelopment(): bool
    {
        return env('APP_ENV', 'production') === 'development';
    }
}

if (!function_exists('isTesting')) {
    /**
     * Check if application is in testing environment
     */
    function isTesting(): bool
    {
        return env('APP_ENV', 'production') === 'testing';
    }
}