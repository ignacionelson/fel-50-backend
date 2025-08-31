<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    public function testAdminDashboardWithAdminUser(): void
    {
        $request = $this->createAdminRequest('GET', '/admin');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Bienvenido al Panel de Administración', $body['message']);
        $this->assertArrayHasKey('admin', $body);
        $this->assertArrayHasKey('timestamp', $body);
        $this->assertEquals($this->testAdminUser->id, $body['admin']['id']);
        $this->assertEquals($this->testAdminUser->email, $body['admin']['email']);
        $this->assertContains('admin', $body['admin']['roles']);
    }

    public function testAdminDashboardWithRegularUser(): void
    {
        $request = $this->createAuthenticatedRequest('GET', '/admin');
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 403, 'Insufficient permissions');
    }

    public function testSystemStatisticsWithAdminUser(): void
    {
        $request = $this->createAdminRequest('GET', '/admin/stats');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Estadísticas del sistema obtenidas exitosamente', $body['message']);
        $this->assertArrayHasKey('stats', $body);
        $this->assertArrayHasKey('total_users', $body['stats']);
        $this->assertArrayHasKey('active_users', $body['stats']);
        $this->assertArrayHasKey('deleted_users', $body['stats']);
        $this->assertArrayHasKey('users_by_role', $body['stats']);
        $this->assertArrayHasKey('system_info', $body['stats']);
        
        // We should have at least 2 users from setup
        $this->assertGreaterThanOrEqual(2, $body['stats']['total_users']);
    }

    public function testListAllUsersWithAdminUser(): void
    {
        $request = $this->createAdminRequest('GET', '/admin/users');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuarios obtenidos exitosamente', $body['message']);
        $this->assertArrayHasKey('users', $body);
        $this->assertArrayHasKey('total_count', $body);
        $this->assertArrayHasKey('filters', $body);
        
        $this->assertIsArray($body['users']);
        $this->assertGreaterThanOrEqual(2, $body['total_count']);
        
        // Check user structure
        $user = $body['users'][0];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('uuid', $user);
        $this->assertArrayHasKey('first_name', $user);
        $this->assertArrayHasKey('last_name', $user);
        $this->assertArrayHasKey('full_name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('roles', $user);
        $this->assertArrayHasKey('status', $user);
    }

    public function testListAllUsersWithDeletedFilter(): void
    {
        $request = $this->createAdminRequest('GET', '/admin/users?include_deleted=true');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('filters', $body);
        $this->assertTrue($body['filters']['include_deleted']);
    }

    public function testActivityLogWithAdminUser(): void
    {
        $request = $this->createAdminRequest('GET', '/admin/activity');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Registro de actividad obtenido exitosamente', $body['message']);
        $this->assertArrayHasKey('activities', $body);
        $this->assertArrayHasKey('total_count', $body);
        
        $this->assertIsArray($body['activities']);
        
        // Check activity structure if any activities exist
        if (!empty($body['activities'])) {
            $activity = $body['activities'][0];
            $this->assertArrayHasKey('id', $activity);
            $this->assertArrayHasKey('user_id', $activity);
            $this->assertArrayHasKey('action', $activity);
            $this->assertArrayHasKey('description', $activity);
            $this->assertArrayHasKey('ip_address', $activity);
            $this->assertArrayHasKey('timestamp', $activity);
        }
    }

    public function testAdminRoutesWithoutAuthentication(): void
    {
        $adminRoutes = ['/admin', '/admin/stats', '/admin/users', '/admin/activity'];

        foreach ($adminRoutes as $route) {
            $request = $this->createRequest('GET', $route);
            $response = $this->app->handle($request);

            $this->assertErrorResponse($response, 401, 'Authorization header missing');
        }
    }
}