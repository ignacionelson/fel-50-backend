<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class UserController
{
    public function deleteUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::find($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        try {
            $user->delete(); // Soft delete
            
            $responseData = [
                'message' => 'Usuario eliminado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'deleted_at' => $user->deleted_at->toISOString()
                ]
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al eliminar usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function restoreUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        if (!$user->trashed()) {
            $response->getBody()->write(json_encode(['error' => 'El usuario no está eliminado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user->restore();
            
            $responseData = [
                'message' => 'Usuario restaurado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'roles' => $user->getRoles()
                ]
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al restaurar usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function forceDeleteUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        try {
            $userData = [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email
            ];

            $user->forceDelete(); // Permanent delete
            
            $responseData = [
                'message' => 'Usuario eliminado permanentemente',
                'user' => $userData
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al eliminar permanentemente el usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getDeletedUsers(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $users = User::onlyTrashed()->get();
            
            $userData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'roles' => $user->getRoles(),
                    'deleted_at' => $user->deleted_at->toISOString()
                ];
            });

            $responseData = [
                'message' => 'Usuarios eliminados obtenidos exitosamente',
                'users' => $userData,
                'total_count' => $users->count()
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al obtener usuarios eliminados']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}