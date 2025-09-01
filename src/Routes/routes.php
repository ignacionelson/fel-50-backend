<?php

use Slim\App;
use App\Middleware\JWTMiddleware;
use App\Middleware\RoleMiddleware;
use App\Controllers\AuthController;
use App\Controllers\ApiController;
use App\Controllers\RoleController;
use App\Controllers\UserController;
use App\Controllers\AdminController;

return function (App $app) {
    // Health check route (outside API versioning)
    $app->get('/health', function ($request, $response) {
        $data = [
            'status' => 'healthy',
            'message' => 'API is online',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'FEL API',
            'version' => '1.0.0'
        ];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // API v1 routes
    $app->group('/api/v1', function ($group) {
        // Public routes (no authentication required)
        $group->post('/register', [AuthController::class, 'register']);
        $group->post('/login', [AuthController::class, 'login']);
        $group->get('/public', [ApiController::class, 'publicRoute']);

        // Protected routes (authentication required)
        $group->group('/private', function ($group) {
            $group->get('/profile', [ApiController::class, 'privateRoute']);
        })->add(new JWTMiddleware());

        // Role management routes (authentication required)
        $group->group('/roles', function ($group) {
            $group->get('', [RoleController::class, 'getAvailableRoles']);
        })->add(new JWTMiddleware());

        // User management routes (authentication required, admin role for management operations)
        $group->group('/users', function ($group) {
            // Admin only routes
            $group->put('/{id}/roles', [RoleController::class, 'assignUserRoles'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->get('/{id}/roles', [RoleController::class, 'getUserRoles'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->get('/{id}/capabilities', [RoleController::class, 'getUserCapabilities'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->delete('/{id}', [UserController::class, 'deleteUser'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->put('/{id}/restore', [UserController::class, 'restoreUser'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->delete('/{id}/force', [UserController::class, 'forceDeleteUser'])
                  ->add(RoleMiddleware::requireRoles('admin'));
            
            $group->get('/deleted', [UserController::class, 'getDeletedUsers'])
                  ->add(RoleMiddleware::requireRoles('admin'));
        })->add(new JWTMiddleware());

        // Admin routes (authentication + admin role required)
        $group->group('/admin', function ($group) {
            $group->get('', [AdminController::class, 'dashboard']);
            $group->get('/stats', [AdminController::class, 'getSystemStats']);
            $group->get('/users', [AdminController::class, 'listAllUsers']);
            $group->get('/activity', [AdminController::class, 'getActivityLog']);
        })->add(RoleMiddleware::requireRoles('admin'))
          ->add(new JWTMiddleware());
    });
};