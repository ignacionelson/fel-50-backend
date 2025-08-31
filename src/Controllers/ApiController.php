<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController
{
    public function publicRoute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $responseData = [
            'message' => 'Esta es una ruta pública - no requiere autenticación',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => [
                'api_version' => '1.0',
                'status' => 'active'
            ]
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function privateRoute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        $user = User::find($userId);

        $responseData = [
            'message' => 'Esta es una ruta privada - autenticación requerida',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}