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
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        try {
            $user->delete(); // Soft delete
            
            $userData = [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'deleted_at' => $user->deleted_at->toISOString()
                ]
            ];

            return successResponse($response, $userData, 'Usuario eliminado exitosamente');

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al eliminar usuario', 500);
        }
    }

    public function restoreUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        if (!$user->trashed()) {
            return errorResponse($response, 'El usuario no está eliminado', 400);
        }

        try {
            $user->restore();
            
            $userData = [
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

            return successResponse($response, $userData, 'Usuario restaurado exitosamente');

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al restaurar usuario', 500);
        }
    }

    public function forceDeleteUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        try {
            $userData = [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email
                ]
            ];

            $user->forceDelete(); // Permanent delete
            
            return successResponse($response, $userData, 'Usuario eliminado permanentemente');

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al eliminar permanentemente el usuario', 500);
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

            $data = [
                'users' => $userData,
                'total_count' => $users->count()
            ];

            return successResponse($response, $data, 'Usuarios eliminados obtenidos exitosamente');

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al obtener usuarios eliminados', 500);
        }
    }
}