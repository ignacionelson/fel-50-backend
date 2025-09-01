# Claude Code Development Notes

## Project Overview
This is a PHP REST API project built with Slim Framework, Eloquent ORM, and JWT authentication.

## CRITICAL: Naming Conventions

### MUST BE IN SPANISH (Argentina):

**Database/Models (EXCEPT User model):**
- Eloquent Model names: `Producto`, `Cliente`, `Factura`, `Pedido`, `Categoria`, `Proveedor`
- Database table names: `productos`, `clientes`, `facturas`, `pedidos`, `categorias`, `proveedores`
- Database columns: `nombre_completo`, `fecha_nacimiento`, `precio_unitario`, `cantidad_stock`, `correo_electronico`
- **EXCEPTION**: User model remains `User` with `users` table (already established)

**API Routes:**
- Route paths: `/api/productos`, `/api/clientes`, `/api/facturas`, `/api/pedidos`
- Route parameters: `/api/productos/{id_producto}`, `/api/clientes/{id_cliente}`
- Query parameters: `?pagina=1&limite=10&orden=fecha_creacion`
- **EXCEPTION**: User routes remain `/users` (already established)

**API Response Messages:**
- ALL user-facing text messages in API responses
- Error messages: `"Usuario no encontrado"`, `"Credenciales inválidas"`, `"Error al procesar solicitud"`
- Success messages: `"Operación exitosa"`, `"Registro creado exitosamente"`, `"Actualización completada"`
- Validation messages: `"Campo requerido"`, `"Formato inválido"`, `"Valor fuera de rango"`

### MUST BE IN US ENGLISH:

**All other code:**
- Variable names: `$userData`, `$productList`, `$response`
- Method names: `getUserById()`, `validateInput()`, `processOrder()`
- Class names (except Models): `ProductController`, `AuthService`, `ValidationMiddleware`
- Constants: `MAX_ATTEMPTS`, `TOKEN_EXPIRY`, `DEFAULT_LIMIT`
- Comments and documentation: All in English

## Development Commitments

### Postman Collection Maintenance
I will **ALWAYS** update the Postman collection file (`postman_collection.json`) whenever I:
- ✅ Create new endpoints
- ✅ Modify existing endpoints  
- ✅ Change request/response formats
- ✅ Delete endpoints
- ✅ Update authentication methods

**CRITICAL: Postman Collection Language Requirements:**
- **User-facing text MUST be in Spanish:** Request names, folder names, descriptions, variable descriptions, API response messages content
- **Technical elements MUST be in English:** HTTP methods, headers, URLs, JSON keys, status codes, technical parameter names, **response example status names** (Success, Error, Created, etc.)
- **Example data MUST use Argentine context:** Names like "Juan Pérez", emails like "juan@ejemplo.com", phone numbers like "+5491134567890"

Examples of correct translations:
- Request names: "Registrar Usuario", "Obtener Productos", "Actualizar Cliente"
- Folder names: "Autenticación", "Gestión de Productos", "Administración"  
- **Response names: "Success", "Validation Error", "Not Found"** (keep technical status terms in English)
- Descriptions: "Endpoint para obtener la lista de productos"
- API response message content: `{"message": "Usuario registrado exitosamente"}` (Spanish content, English keys)

This ensures the API documentation stays synchronized with the actual implementation and provides a localized Spanish experience.

## Current Architecture

### Framework & Libraries
- **Slim Framework 4** - Lightweight PHP framework
- **Eloquent ORM** - Database operations with Laravel's Eloquent
- **Firebase JWT** - JSON Web Token authentication
- **Respect Validation** - Request validation
- **PHP DotEnv** - Environment configuration

### Project Structure
```
fel-api/
├── public/
│   └── index.php          # Application entry point
├── src/
│   ├── Controllers/       # Request handlers
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── RoleController.php
│   │   └── UserController.php
│   ├── Middleware/        # Request middleware
│   │   ├── JWTMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Models/           # Eloquent models
│   │   └── User.php
│   ├── Routes/           # Application routes
│   │   └── routes.php    # Route definitions
│   ├── Services/         # Business logic services
│   │   ├── JWTService.php
│   │   ├── RoleService.php
│   │   └── Validator.php
│   └── Traits/           # Reusable traits
│       └── HasUuid.php   # UUID generation trait
├── db/
│   ├── migrations/       # Phinx migration files
│   └── seeds/           # Database seed files
├── config/
│   └── roles.php         # Role and capability definitions
├── phinx.php            # Phinx configuration
├── postman_collection.json # API documentation for Postman
├── database.sql          # Database initialization
├── .env                  # Environment configuration
└── composer.json         # Dependencies
```

### Current Endpoints

**Authentication:**
- `POST /register` - User registration
- `POST /login` - User authentication  

**Public Routes:**
- `GET /health` - Health check endpoint (no auth)
- `GET /public` - Public route (no auth)

**Protected Routes:**
- `GET /private/profile` - User profile (JWT required)

**Admin Routes (admin role required):**
- `GET /admin` - Admin dashboard
- `GET /admin/stats` - System statistics
- `GET /admin/users` - List all users (with optional deleted filter)
- `GET /admin/activity` - Activity log

**User Management (admin only):**
- `PUT /users/:id/roles` - Assign roles to user
- `GET /users/:id/roles` - Get user roles  
- `GET /users/:id/capabilities` - Get user capabilities
- `DELETE /users/:id` - Soft delete user
- `PUT /users/:id/restore` - Restore soft deleted user
- `DELETE /users/:id/force` - Permanently delete user
- `GET /users/deleted` - List soft deleted users

**Role Management:**
- `GET /roles` - Get available roles and capabilities (authenticated)

## Development Roadmap

*To be filled as new features and improvements are planned*

### Upcoming Features
- [ ] TBD

### Technical Improvements
- [ ] TBD

### Bug Fixes
- [ ] TBD

## Testing

### Test Structure
The project uses **PHPUnit 10** for unit and feature testing:

```
tests/
├── bootstrap.php          # Test bootstrap file
├── TestCase.php          # Base test class with helpers
├── Unit/                 # Unit tests for individual classes
│   ├── UserModelTest.php
│   └── JWTServiceTest.php
└── Feature/              # Integration tests for API endpoints
    ├── AuthenticationTest.php
    ├── PublicRoutesTest.php
    ├── PrivateRoutesTest.php
    ├── AdminRoutesTest.php
    └── UserRoleManagementTest.php
```

### Running Tests
```bash
# Run all tests
composer test

# Run with coverage report
composer test-coverage

# Run specific test file
vendor/bin/phpunit tests/Feature/AuthenticationTest.php

# Run specific test method
vendor/bin/phpunit --filter testUserLoginSuccess tests/Feature/AuthenticationTest.php
```

### Test Database
- Uses in-memory SQLite for fast, isolated tests
- Each test case sets up and tears down its own database schema
- Test users are automatically created for authentication testing:
  - Regular user: `test@example.com` / `password123` (roles: `visitante`)
  - Admin user: `admin@example.com` / `password123` (roles: `admin`)

### Writing Tests
Always extend the base `TestCase` class which provides:
- `createRequest()` - Create HTTP requests
- `createAuthenticatedRequest()` - Create requests with user token
- `createAdminRequest()` - Create requests with admin token
- `assertSuccessResponse()` - Assert successful API response
- `assertErrorResponse()` - Assert error API response
- `assertValidationErrorResponse()` - Assert validation errors

Example test:
```php
public function testCreateProducto(): void
{
    $productData = [
        'nombre' => 'Producto Test',
        'precio_unitario' => 99.99,
        'cantidad_stock' => 10
    ];

    $request = $this->createAuthenticatedRequest('POST', '/productos', $productData);
    $response = $this->app->handle($request);

    $body = $this->assertSuccessResponse($response, 201);
    $this->assertEquals('Producto creado exitosamente', $body['message']);
}
```

### Maintaining Tests
**ALWAYS update tests when:**
- Adding new endpoints
- Modifying existing endpoints
- Changing request/response formats
- Adding new models or services

## Database Management

### Migration System
The project uses **Phinx** for database schema management:

**Commands:**
- `vendor/bin/phinx migrate` - Run all pending migrations
- `vendor/bin/phinx rollback` - Rollback the last migration
- `vendor/bin/phinx status` - Show migration status
- `vendor/bin/phinx create MigrationName` - Create a new migration file

**Migration Files Location:** `db/migrations/`
**Configuration:** `phinx.php`

### Initial Setup
1. Run `mysql < database.sql` to create the database
2. Run `vendor/bin/phinx migrate` to create all tables

### Important Migration Rules
**ALWAYS create a new migration when:**
- Adding new fields to models
- Modifying existing field types, constraints, or properties  
- Removing fields from models
- Adding or removing indexes
- Changing table structure in any way

**Never modify existing migration files** - always create new ones for schema changes. This ensures proper version control and allows rollbacks.

## User Role System

### Available Roles
- **admin** - Full system access with all capabilities
- **expositor** - Exhibition management and content creation
- **visitante** - Basic visitor access (read-only)
- **profesional** - Professional networking and content access
- **prensa** - Press and media access

### Role Configuration
- Roles are defined in `config/roles.php` (file-based, not database)
- Each role has specific capabilities
- Users can have multiple roles assigned
- Capabilities are checked via `RoleService` and `RoleMiddleware`

### Usage Examples
```php
// Protect route with role
$app->get('/admin/users', UserController::class . ':list')
    ->add(RoleMiddleware::requireRoles('admin'));

// Protect route with capability
$app->post('/content', ContentController::class . ':create')
    ->add(RoleMiddleware::requireCapabilities('content.create'));
```

## Notes
- Database: MySQL with `api_rest` schema
- Authentication: JWT with Bearer token format
- Authorization: Role-based access control with file-based capabilities
- Validation: Comprehensive request validation on all endpoints
- Error Handling: Structured JSON error responses
- Migrations: Phinx migration system for database schema management

## Code Examples with Naming Conventions

### Creating a New Model (Spanish name, except User)
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Producto extends Model
{
    use HasUuid;
    
    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'descripcion', 
        'precio_unitario',
        'cantidad_stock',
        'categoria_id',
        'activo'
    ];
    
    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean'
    ];
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
```

### Controller with Spanish Routes and Messages (English code)
```php
<?php
namespace App\Controllers;

use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController extends ApiController
{
    public function list(Request $request, Response $response): Response
    {
        // English variable names
        $queryParams = $request->getQueryParams();
        $page = $queryParams['pagina'] ?? 1;  // Spanish query param
        $limit = $queryParams['limite'] ?? 10; // Spanish query param
        
        $products = Producto::query()
            ->where('activo', true)
            ->paginate($limit, ['*'], 'pagina', $page);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Productos obtenidos exitosamente', // Spanish message
            'data' => $products->items(),
            'pagination' => [
                'pagina_actual' => $products->currentPage(),
                'total_paginas' => $products->lastPage(),
                'total_registros' => $products->total()
            ]
        ]);
    }
    
    public function getById(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id_producto']; // Spanish route param
        
        $product = Producto::find($productId);
        
        if (!$product) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Producto no encontrado' // Spanish error message
            ], 404);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Producto obtenido exitosamente', // Spanish message
            'data' => $product
        ]);
    }
}
```

### Route Definitions (Spanish paths)
```php
// src/Routes/routes.php

// Products routes (Spanish)
$app->group('/productos', function ($group) {
    $group->get('', ProductController::class . ':list');
    $group->get('/{id_producto}', ProductController::class . ':getById');
    $group->post('', ProductController::class . ':create');
    $group->put('/{id_producto}', ProductController::class . ':update');
    $group->delete('/{id_producto}', ProductController::class . ':delete');
})->add(JWTMiddleware::class);

// Orders routes (Spanish)
$app->group('/pedidos', function ($group) {
    $group->get('', OrderController::class . ':list');
    $group->get('/{id_pedido}', OrderController::class . ':getById');
    $group->post('', OrderController::class . ':create');
    $group->put('/{id_pedido}/estado', OrderController::class . ':updateStatus');
})->add(JWTMiddleware::class);

// User routes remain in English (existing)
$app->group('/users', function ($group) {
    $group->get('/{id}', UserController::class . ':getById');
    $group->put('/{id}/roles', UserController::class . ':assignRoles');
})->add(RoleMiddleware::requireRoles('admin'));
```

### Migration with Spanish Table Names
```php
<?php
use Phinx\Migration\AbstractMigration;

class CreateProductosTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('productos');
        $table->addColumn('uuid', 'string', ['limit' => 36])
              ->addColumn('nombre', 'string', ['limit' => 255])
              ->addColumn('descripcion', 'text', ['null' => true])
              ->addColumn('precio_unitario', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('cantidad_stock', 'integer', ['default' => 0])
              ->addColumn('categoria_id', 'integer', ['null' => true])
              ->addColumn('activo', 'boolean', ['default' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['uuid'], ['unique' => true])
              ->addIndex(['categoria_id'])
              ->addIndex(['activo'])
              ->create();
    }
}
```