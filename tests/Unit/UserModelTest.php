<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserModelTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'phone_number' => '+1234567890'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Jane', $user->first_name);
        $this->assertEquals('Smith', $user->last_name);
        $this->assertEquals('jane@example.com', $user->email);
        $this->assertNotNull($user->uuid);
        $this->assertTrue(strlen($user->uuid) > 0);
    }

    public function testUserFullNameAttribute(): void
    {
        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function testPasswordHashing(): void
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'password.test@example.com',
            'password' => 'plaintext'
        ]);

        // Password should be hashed
        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue($user->verifyPassword('plaintext'));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    public function testUserRoles(): void
    {
        $user = User::create([
            'first_name' => 'Role',
            'last_name' => 'User',
            'email' => 'roles@example.com',
            'password' => 'password123',
            'roles' => ['admin', 'expositor']
        ]);

        $roles = $user->getRoles();
        $this->assertIsArray($roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('expositor', $roles);
    }

    public function testUserSoftDelete(): void
    {
        $user = User::create([
            'first_name' => 'Delete',
            'last_name' => 'Test',
            'email' => 'delete@example.com',
            'password' => 'password123'
        ]);

        $userId = $user->id;
        
        // Soft delete
        $user->delete();
        
        // Should not be found in regular query
        $this->assertNull(User::find($userId));
        
        // Should be found with trashed
        $this->assertNotNull(User::withTrashed()->find($userId));
        $this->assertTrue(User::withTrashed()->find($userId)->trashed());
    }

    public function testUserRestore(): void
    {
        $user = User::create([
            'first_name' => 'Restore',
            'last_name' => 'Test',
            'email' => 'restore@example.com',
            'password' => 'password123'
        ]);

        $userId = $user->id;
        
        // Soft delete and restore
        $user->delete();
        $user->restore();
        
        // Should be found in regular query again
        $restoredUser = User::find($userId);
        $this->assertNotNull($restoredUser);
        $this->assertFalse($restoredUser->trashed());
    }

    public function testUuidGeneration(): void
    {
        $user1 = User::create([
            'first_name' => 'UUID1',
            'last_name' => 'Test',
            'email' => 'uuid1@example.com',
            'password' => 'password123'
        ]);

        $user2 = User::create([
            'first_name' => 'UUID2',
            'last_name' => 'Test',
            'email' => 'uuid2@example.com',
            'password' => 'password123'
        ]);

        // UUIDs should be unique
        $this->assertNotEquals($user1->uuid, $user2->uuid);
        
        // UUID format validation (basic check)
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user1->uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user2->uuid);
    }
}