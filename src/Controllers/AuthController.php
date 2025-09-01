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
            return validationErrorResponse($response, $errors);
        }

        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            return errorResponse($response, 'El correo electrónico ya existe', 409);
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

            $userData = [
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

            return successResponse($response, $userData, 'Usuario registrado exitosamente', 201);

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al registrar usuario', 500);
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $errors = Validator::validate($data, Validator::userLogin());
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $user = User::where('email', $data['email'])->first();
        
        if (!$user || !$user->verifyPassword($data['password'])) {
            return errorResponse($response, 'Credenciales inválidas', 401);
        }

        $token = JWTService::generateToken($user->id);

        $userData = [
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

        return successResponse($response, $userData, 'Inicio de sesión exitoso');
    }
}