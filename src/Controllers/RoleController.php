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
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $response->getBody()->write(json_encode(['error' => 'Se requiere un array de roles']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        foreach ($data['roles'] as $role) {
            if (!RoleService::validateRole($role)) {
                $response->getBody()->write(json_encode(['error' => "Rol inválido: {$role}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
            }
        }

        try {
            $user->roles = $data['roles'];
            $user->save();

            $responseData = [
                'message' => 'Roles de usuario actualizados exitosamente',
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
            $response->getBody()->write(json_encode(['error' => 'Error al actualizar roles de usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getUserRoles(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::find($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $responseData = [
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
    }

    public function getUserCapabilities(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $userId = $args['id'];
        $user = User::find($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $capabilities = RoleService::getUserCapabilities($user);
        $capabilityDescriptions = [];
        
        foreach ($capabilities as $capability) {
            $capabilityDescriptions[$capability] = RoleService::getCapabilityDescription($capability);
        }

        $responseData = [
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

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
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

        $responseData = [
            'roles' => $availableRoles,
            'total_count' => count($availableRoles)
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}