# PHP REST API with Slim, Eloquent, and JWT

A complete REST API built with PHP using Slim Framework, Eloquent ORM, and JWT authentication.

## Features

- **Slim Framework 4**: Fast and lightweight PHP framework
- **Eloquent ORM**: Database operations with Laravel's Eloquent
- **JWT Authentication**: Secure token-based authentication
- **Validation**: Request validation using Respect/Validation
- **Password Hashing**: Secure password storage with bcrypt

## Installation

1. Install dependencies:
```bash
composer install
```

2. Setup database:
- Create a MySQL database named `api_rest`
- Import the database schema: `mysql -u root -p api_rest < database.sql`

3. Configure environment:
- Copy `.env.example` to `.env`
- Update database credentials and JWT secret in `.env`

4. Start the development server:
```bash
composer start
# or
php -S localhost:8000 -t public
```

## API Endpoints

### Public Routes
- `GET /public` - Public route (no authentication required)

### Authentication
- `POST /register` - User registration
- `POST /login` - User login

### Private Routes (JWT required)
- `GET /private/profile` - Get user profile (requires Authorization header)

## Usage Examples

### Register
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Access Private Route
```bash
curl -X GET http://localhost:8000/private/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Project Structure

```
├── public/
│   └── index.php          # Application entry point
├── src/
│   ├── Controllers/       # Request handlers
│   ├── Middleware/        # JWT middleware
│   ├── Models/           # Eloquent models
│   └── Services/         # JWT and validation services
├── .env                  # Environment configuration
└── composer.json         # Dependencies
```