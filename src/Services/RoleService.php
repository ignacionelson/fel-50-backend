<?php

namespace App\Services;

class RoleService
{
    private static $rolesConfig;

    private static function loadConfig()
    {
        if (self::$rolesConfig === null) {
            self::$rolesConfig = require __DIR__ . '/../../config/roles.php';
        }
        return self::$rolesConfig;
    }

    public static function getAllRoles()
    {
        $config = self::loadConfig();
        return $config['roles'];
    }

    public static function getRole($roleName)
    {
        $config = self::loadConfig();
        return $config['roles'][$roleName] ?? null;
    }

    public static function getRoleCapabilities($roleName)
    {
        $role = self::getRole($roleName);
        return $role ? $role['capabilities'] : [];
    }

    public static function getUserCapabilities($user)
    {
        $capabilities = [];
        $userRoles = $user->getRoles();
        
        foreach ($userRoles as $roleName) {
            $roleCapabilities = self::getRoleCapabilities($roleName);
            $capabilities = array_merge($capabilities, $roleCapabilities);
        }
        
        return array_unique($capabilities);
    }

    public static function userHasCapability($user, $capability)
    {
        $userCapabilities = self::getUserCapabilities($user);
        return in_array($capability, $userCapabilities);
    }

    public static function userHasAnyCapability($user, $capabilities)
    {
        $userCapabilities = self::getUserCapabilities($user);
        return !empty(array_intersect($capabilities, $userCapabilities));
    }

    public static function userHasAllCapabilities($user, $capabilities)
    {
        $userCapabilities = self::getUserCapabilities($user);
        return empty(array_diff($capabilities, $userCapabilities));
    }

    public static function validateRole($roleName)
    {
        $config = self::loadConfig();
        return isset($config['roles'][$roleName]);
    }

    public static function getAvailableRoles()
    {
        $config = self::loadConfig();
        return array_keys($config['roles']);
    }

    public static function getCapabilityDescription($capability)
    {
        $config = self::loadConfig();
        return $config['capabilities'][$capability] ?? $capability;
    }

    public static function getAllCapabilities()
    {
        $config = self::loadConfig();
        return $config['capabilities'];
    }
}