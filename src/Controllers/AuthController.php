<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\JWTService;
use App\Services\Validator;
use App\Services\EmailService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class AuthController
{
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);

        // Validar solo email
        $errors = Validator::validate($data, [
            'email' => 'required|email'
        ]);
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            if ($existingUser->isPendingVerification()) {
                // Reenviar código de verificación si la cuenta no está verificada
                $verificationCode = $existingUser->generateVerificationCode();
                $existingUser->save();

                $emailService = new EmailService();
                $emailService->sendVerificationEmail($existingUser->email, $verificationCode);

                return successResponse($response, [
                    'email' => $existingUser->email
                ], 'Se ha reenviado el código de verificación a tu correo electrónico');
            }
            return errorResponse($response, 'El correo electrónico ya está registrado y verificado', 409);
        }

        try {
            // Crear usuario solo con email
            $user = User::create([
                'email' => $data['email'],
                'email_verified' => false,
                'account_status' => 'pending',
                'roles' => ['visitante'] // Rol por defecto
            ]);

            // Generar código de verificación
            $verificationCode = $user->generateVerificationCode();
            $user->save();

            // Enviar email de verificación
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($user->email, $verificationCode);

            if (!$emailSent) {
                error_log("Error enviando email de verificación a: {$user->email}");
            }

            $responseData = [
                'email' => $user->email,
                'message_detail' => 'Te hemos enviado un correo con el código de verificación. Por favor revisa tu bandeja de entrada.'
            ];

            return successResponse($response, $responseData, 'Registro exitoso. Por favor verifica tu correo electrónico.', 201);

        } catch (\Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            return errorResponse($response, 'Error al registrar usuario', 500);
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);

        $errors = Validator::validate($data, Validator::userLogin());
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !$user->verifyPassword($data['password'])) {
            return errorResponse($response, 'Credenciales inválidas', 401);
        }

        // Verificar si la cuenta está activa
        if (!$user->isActive()) {
            if ($user->isPendingVerification()) {
                return errorResponse($response, 'Tu cuenta aún no ha sido verificada. Por favor revisa tu correo electrónico.', 403);
            }
            return errorResponse($response, 'Tu cuenta no está activa. Contacta al administrador.', 403);
        }

        $token = JWTService::generateToken($user->id);

        $userData = [
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'token' => $token
        ];

        return successResponse($response, $userData, 'Inicio de sesión exitoso');
    }

    /**
     * Verificar cuenta con código de verificación
     */
    public function verifyAccount(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);

        // Si vienen como query params (desde el link del email)
        $queryParams = $request->getQueryParams();
        if (!empty($queryParams['email']) && !empty($queryParams['code'])) {
            $data = $queryParams;
        }

        // Validar datos
        $errors = Validator::validate($data, [
            'email' => 'required|email',
            'code' => 'required|string'
        ]);
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        if ($user->email_verified) {
            return successResponse($response, [
                'email' => $user->email
            ], 'Tu cuenta ya ha sido verificada anteriormente');
        }

        if (!$user->isVerificationCodeValid($data['code'])) {
            return errorResponse($response, 'Código de verificación inválido o expirado', 400);
        }

        try {
            // Marcar como verificado
            $user->markEmailAsVerified();
            $user->save();

            // Enviar email de bienvenida
            $emailService = new EmailService();
            $emailService->sendWelcomeEmail($user->email, $user->full_name);

            // Generar token para login automático
            $token = JWTService::generateToken($user->id);

            $userData = [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'account_status' => $user->account_status
                ],
                'token' => $token,
                'next_step' => 'complete_profile' // Indicar que debe completar su perfil
            ];

            return successResponse($response, $userData, 'Cuenta verificada exitosamente');

        } catch (\Exception $e) {
            error_log("Error verificando cuenta: " . $e->getMessage());
            return errorResponse($response, 'Error al verificar la cuenta', 500);
        }
    }

    /**
     * Reenviar código de verificación
     */
    public function resendVerificationCode(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);

        $errors = Validator::validate($data, [
            'email' => 'required|email'
        ]);
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        if ($user->email_verified) {
            return errorResponse($response, 'Esta cuenta ya ha sido verificada', 400);
        }

        try {
            // Generar nuevo código
            $verificationCode = $user->generateVerificationCode();
            $user->save();

            // Enviar email
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($user->email, $verificationCode);

            if (!$emailSent) {
                return errorResponse($response, 'Error al enviar el correo electrónico', 500);
            }

            return successResponse($response, [
                'email' => $user->email
            ], 'Se ha enviado un nuevo código de verificación a tu correo electrónico');

        } catch (\Exception $e) {
            error_log("Error reenviando código: " . $e->getMessage());
            return errorResponse($response, 'Error al reenviar el código', 500);
        }
    }

    /**
     * Completar perfil después de verificación
     */
    public function completeProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('userId');
        $data = json_decode($request->getBody()->getContents(), true);

        // Validar datos del perfil
        $errors = Validator::validate($data, [
            'first_name' => 'required|string|min:2|max:100',
            'last_name' => 'required|string|min:2|max:100',
            'password' => 'required|string|min:6',
            'phone_number' => 'optional|phone'
        ]);
        if ($errors) {
            return validationErrorResponse($response, $errors);
        }

        $user = User::find($userId);

        if (!$user) {
            return errorResponse($response, 'Usuario no encontrado', 404);
        }

        if (!$user->email_verified) {
            return errorResponse($response, 'Debes verificar tu cuenta antes de completar el perfil', 403);
        }

        try {
            // Actualizar perfil
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->password = $data['password'];
            $user->phone_number = $data['phone_number'] ?? null;
            $user->save();

            $userData = [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'email_verified' => $user->email_verified,
                    'account_status' => $user->account_status
                ]
            ];

            return successResponse($response, $userData, 'Perfil completado exitosamente');

        } catch (\Exception $e) {
            error_log("Error completando perfil: " . $e->getMessage());
            return errorResponse($response, 'Error al completar el perfil', 500);
        }
    }
}