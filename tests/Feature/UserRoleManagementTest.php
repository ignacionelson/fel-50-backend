<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserRoleManagementTest extends TestCase
{
    public function testAssignRolesToUser(): void
    {
        $roleData = [
            'roles' => ['admin', 'expositor']
        ];

        $request = $this->createAdminRequest('PUT', '/users/' . $this->testUser->id . '/roles', $roleData);
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Roles de usuario actualizados exitosamente', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertContains('admin', $body['user']['roles']);
        $this->assertContains('expositor', $body['user']['roles']);
    }

    public function testAssignRolesWithInvalidRole(): void
    {
        $roleData = [
            'roles' => ['admin', 'invalid_role']
        ];

        $request = $this->createAdminRequest('PUT', '/users/' . $this->testUser->id . '/roles', $roleData);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 422, 'Rol inválido: invalid_role');
    }

    public function testAssignRolesWithMissingRolesArray(): void
    {
        $request = $this->createAdminRequest('PUT', '/users/' . $this->testUser->id . '/roles', []);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 422, 'Se requiere un array de roles');
    }

    public function testAssignRolesToNonExistentUser(): void
    {
        $roleData = [
            'roles' => ['admin']
        ];

        $request = $this->createAdminRequest('PUT', '/users/99999/roles', $roleData);
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 404, 'Usuario no encontrado');
    }

    public function testGetUserRoles(): void
    {
        $request = $this->createAdminRequest('GET', '/users/' . $this->testAdminUser->id . '/roles');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('roles', $body['user']);
        $this->assertContains('admin', $body['user']['roles']);
        $this->assertEquals($this->testAdminUser->id, $body['user']['id']);
        $this->assertEquals($this->testAdminUser->email, $body['user']['email']);
    }

    public function testGetUserRolesForNonExistentUser(): void
    {
        $request = $this->createAdminRequest('GET', '/users/99999/roles');
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 404, 'Usuario no encontrado');
    }

    public function testGetUserCapabilities(): void
    {
        $request = $this->createAdminRequest('GET', '/users/' . $this->testAdminUser->id . '/capabilities');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('capabilities', $body);
        $this->assertArrayHasKey('capability_descriptions', $body);
        $this->assertIsArray($body['capabilities']);
        $this->assertIsArray($body['capability_descriptions']);
        
        // Admin should have capabilities
        $this->assertNotEmpty($body['capabilities']);
    }

    public function testGetAvailableRoles(): void
    {
        $request = $this->createAuthenticatedRequest('GET', '/roles');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('roles', $body);
        $this->assertArrayHasKey('total_count', $body);
        $this->assertIsArray($body['roles']);
        $this->assertGreaterThan(0, $body['total_count']);
        
        // Check role structure
        $role = $body['roles'][0];
        $this->assertArrayHasKey('name', $role);
        $this->assertArrayHasKey('display_name', $role);
        $this->assertArrayHasKey('description', $role);
        $this->assertArrayHasKey('capabilities', $role);
    }

    public function testSoftDeleteUser(): void
    {
        $request = $this->createAdminRequest('DELETE', '/users/' . $this->testUser->id);
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuario eliminado exitosamente', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertArrayHasKey('deleted_at', $body['user']);
        $this->assertNotNull($body['user']['deleted_at']);
    }

    public function testRestoreUser(): void
    {
        // First soft delete the user
        $this->testUser->delete();

        $request = $this->createAdminRequest('PUT', '/users/' . $this->testUser->id . '/restore');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuario restaurado exitosamente', $body['message']);
        $this->assertArrayHasKey('user', $body);
        $this->assertEquals($this->testUser->id, $body['user']['id']);
    }

    public function testRestoreUserThatIsNotDeleted(): void
    {
        $request = $this->createAdminRequest('PUT', '/users/' . $this->testUser->id . '/restore');
        $response = $this->app->handle($request);

        $this->assertErrorResponse($response, 400, 'El usuario no está eliminado');
    }

    public function testForceDeleteUser(): void
    {
        // Create a temporary user to delete
        $tempUser = User::create([
            'first_name' => 'Temp',
            'last_name' => 'User',
            'email' => 'temp@example.com',
            'password' => 'password123'
        ]);

        $request = $this->createAdminRequest('DELETE', '/users/' . $tempUser->id . '/force');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuario eliminado permanentemente', $body['message']);
        $this->assertArrayHasKey('user', $body);
        
        // Verify user is completely gone
        $this->assertNull(User::withTrashed()->find($tempUser->id));
    }

    public function testGetDeletedUsers(): void
    {
        // Create and soft delete a user
        $tempUser = User::create([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => 'deleted@example.com',
            'password' => 'password123'
        ]);
        $tempUser->delete();

        $request = $this->createAdminRequest('GET', '/users/deleted');
        $response = $this->app->handle($request);

        $body = $this->assertSuccessResponse($response, 200);
        
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Usuarios eliminados obtenidos exitosamente', $body['message']);
        $this->assertArrayHasKey('users', $body);
        $this->assertArrayHasKey('total_count', $body);
        $this->assertIsArray($body['users']);
        $this->assertGreaterThanOrEqual(1, $body['total_count']);
        
        // Check that all returned users have deleted_at timestamp
        foreach ($body['users'] as $user) {
            $this->assertArrayHasKey('deleted_at', $user);
            $this->assertNotNull($user['deleted_at']);
        }
    }

    public function testRoleManagementWithoutAdminPermissions(): void
    {
        $roleRoutes = [
            ['PUT', '/users/' . $this->testUser->id . '/roles'],
            ['GET', '/users/' . $this->testUser->id . '/roles'],
            ['GET', '/users/' . $this->testUser->id . '/capabilities'],
            ['DELETE', '/users/' . $this->testUser->id],
            ['PUT', '/users/' . $this->testUser->id . '/restore'],
            ['DELETE', '/users/' . $this->testUser->id . '/force'],
            ['GET', '/users/deleted']
        ];

        foreach ($roleRoutes as $route) {
            $method = $route[0];
            $path = $route[1];
            
            $request = $this->createAuthenticatedRequest($method, $path, []);
            $response = $this->app->handle($request);

            $this->assertErrorResponse($response, 403, 'Insufficient permissions');
        }
    }
}