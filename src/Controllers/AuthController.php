<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\JWTService;
use App\Services\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class AuthController
{
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $errors = Validator::validate($data, Validator::userRegistration());
        if ($errors) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            $response->getBody()->write(json_encode(['error' => 'El correo electrónico ya existe']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone_number' => $data['phone_number'] ?? null
            ]);

            $token = JWTService::generateToken($user->id);

            $responseData = [
                'message' => 'Usuario registrado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number
                ],
                'token' => $token
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al registrar usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $errors = Validator::validate($data, Validator::userLogin());
        if ($errors) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        $user = User::where('email', $data['email'])->first();
        
        if (!$user || !$user->verifyPassword($data['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales inválidas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = JWTService::generateToken($user->id);

        $responseData = [
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'token' => $token
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}