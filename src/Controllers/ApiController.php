<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController
{
    public function publicRoute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => [
                'api_version' => '1.0',
                'status' => 'active'
            ]
        ];

        return successResponse($response, $data, 'Esta es una ruta pública - no requiere autenticación');
    }

    public function privateRoute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        $user = User::find($userId);

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'created_at' => $user->created_at
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return successResponse($response, $data, 'Esta es una ruta privada - autenticación requerida');
    }
}