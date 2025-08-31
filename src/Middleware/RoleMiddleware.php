<?php

namespace App\Middleware;

use App\Models\User;
use App\Services\RoleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware
{
    private $requiredRoles;
    private $requiredCapabilities;
    private $requireAll;

    public function __construct($roles = [], $capabilities = [], $requireAll = false)
    {
        $this->requiredRoles = is_array($roles) ? $roles : [$roles];
        $this->requiredCapabilities = is_array($capabilities) ? $capabilities : [$capabilities];
        $this->requireAll = $requireAll;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return $this->createErrorResponse('Unauthorized', 401);
        }

        $user = User::find($userId);
        
        if (!$user) {
            return $this->createErrorResponse('User not found', 401);
        }

        if (!empty($this->requiredRoles)) {
            $hasRole = $this->requireAll 
                ? $this->userHasAllRoles($user, $this->requiredRoles)
                : $user->hasAnyRole($this->requiredRoles);
                
            if (!$hasRole) {
                return $this->createErrorResponse('Insufficient role privileges', 403);
            }
        }

        if (!empty($this->requiredCapabilities)) {
            $hasCapability = $this->requireAll
                ? RoleService::userHasAllCapabilities($user, $this->requiredCapabilities)
                : RoleService::userHasAnyCapability($user, $this->requiredCapabilities);
                
            if (!$hasCapability) {
                return $this->createErrorResponse('Insufficient capabilities', 403);
            }
        }

        $request = $request->withAttribute('user', $user);
        return $handler->handle($request);
    }

    private function userHasAllRoles($user, $roles)
    {
        foreach ($roles as $role) {
            if (!$user->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    private function createErrorResponse($message, $statusCode)
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    public static function requireRoles(...$roles)
    {
        return new self($roles);
    }

    public static function requireCapabilities(...$capabilities)
    {
        return new self([], $capabilities);
    }

    public static function requireAllRoles(...$roles)
    {
        return new self($roles, [], true);
    }

    public static function requireAllCapabilities(...$capabilities)
    {
        return new self([], $capabilities, true);
    }
}