<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\RoleService;
use App\Services\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RoleController
{
    public function assignUserRoles(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        
        $user = User::find($userId);
        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            return errorResponse($response, 'Se requiere un array de roles', 422);
        }

        foreach ($data['roles'] as $role) {
            if (!RoleService::validateRole($role)) {
                return errorResponse($response, "Rol inválido: {$role}", 422);
            }
        }

        try {
            $user->roles = $data['roles'];
            $user->save();

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

            return successResponse($response, $userData, 'Roles de usuario actualizados exitosamente');

        } catch (\Exception $e) {
            return errorResponse($response, 'Error al actualizar roles de usuario', 500);
        }
    }

    public function getUserRoles(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::find($userId);
        
        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

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

        return successResponse($response, $userData);
    }

    public function getUserCapabilities(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::find($userId);
        
        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        $capabilities = RoleService::getUserCapabilities($user);
        $capabilityDescriptions = [];
        
        foreach ($capabilities as $capability) {
            $capabilityDescriptions[$capability] = RoleService::getCapabilityDescription($capability);
        }

        $data = [
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'roles' => $user->getRoles()
            ],
            'capabilities' => $capabilities,
            'capability_descriptions' => $capabilityDescriptions
        ];

        return successResponse($response, $data);
    }

    public function getAvailableRoles(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $roles = RoleService::getAllRoles();
        $availableRoles = [];

        foreach ($roles as $roleName => $roleConfig) {
            $availableRoles[] = [
                'name' => $roleName,
                'display_name' => $roleConfig['name'],
                'description' => $roleConfig['description'],
                'capabilities' => $roleConfig['capabilities']
            ];
        }

        $data = [
            'roles' => $availableRoles,
            'total_count' => count($availableRoles)
        ];

        return successResponse($response, $data);
    }
}