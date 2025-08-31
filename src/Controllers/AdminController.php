<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class AdminController
{
    public function dashboard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $adminUser = $request->getAttribute('user');
        
        $responseData = [
            'message' => 'Bienvenido al Panel de Administración',
            'admin' => [
                'id' => $adminUser->id,
                'uuid' => $adminUser->uuid,
                'first_name' => $adminUser->first_name,
                'last_name' => $adminUser->last_name,
                'full_name' => $adminUser->full_name,
                'email' => $adminUser->email,
                'roles' => $adminUser->getRoles()
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getSystemStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::count(),
                'deleted_users' => User::onlyTrashed()->count(),
                'users_by_role' => $this->getUsersByRole(),
                'system_info' => [
                    'api_version' => '1.0.0',
                    'php_version' => phpversion(),
                    'server_time' => date('Y-m-d H:i:s'),
                    'timezone' => date_default_timezone_get()
                ]
            ];

            $responseData = [
                'message' => 'Estadísticas del sistema obtenidas exitosamente',
                'stats' => $stats
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al obtener estadísticas del sistema']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function listAllUsers(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $queryParams = $request->getQueryParams();
            $includeDeleted = isset($queryParams['include_deleted']) && $queryParams['include_deleted'] === 'true';
            
            if ($includeDeleted) {
                $users = User::withTrashed()->get();
            } else {
                $users = User::all();
            }

            $userData = $users->map(function ($user) {
                $data = [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'roles' => $user->getRoles(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString()
                ];

                if ($user->trashed()) {
                    $data['deleted_at'] = $user->deleted_at->toISOString();
                    $data['status'] = 'deleted';
                } else {
                    $data['status'] = 'active';
                }

                return $data;
            });

            $responseData = [
                'message' => 'Usuarios obtenidos exitosamente',
                'users' => $userData,
                'total_count' => $users->count(),
                'filters' => [
                    'include_deleted' => $includeDeleted
                ]
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al obtener usuarios']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getActivityLog(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // This is a placeholder for activity logging functionality
        // In a production system, you would track user actions in a separate activity_logs table
        
        $responseData = [
            'message' => 'Registro de actividad obtenido exitosamente',
            'activities' => [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'action' => 'user.login',
                    'description' => 'Usuario inició sesión',
                    'ip_address' => '127.0.0.1',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ],
                [
                    'id' => 2,
                    'user_id' => 1,
                    'action' => 'user.role.update',
                    'description' => 'Roles de usuario actualizados',
                    'ip_address' => '127.0.0.1',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
                ]
            ],
            'total_count' => 2
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function getUsersByRole(): array
    {
        $users = User::all();
        $roleStats = [];
        
        foreach ($users as $user) {
            $roles = $user->getRoles();
            foreach ($roles as $role) {
                if (!isset($roleStats[$role])) {
                    $roleStats[$role] = 0;
                }
                $roleStats[$role]++;
            }
        }

        // Add zero counts for roles with no users
        $availableRoles = ['admin', 'expositor', 'visitante', 'profesional', 'prensa'];
        foreach ($availableRoles as $role) {
            if (!isset($roleStats[$role])) {
                $roleStats[$role] = 0;
            }
        }

        return $roleStats;
    }
}