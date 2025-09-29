<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddVerificationFieldsToUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        // Agregar campos para verificación de cuenta
        $table->addColumn('email_verified', 'boolean', [
            'default' => false,
            'after' => 'email',
            'comment' => 'Indica si el email ha sido verificado'
        ])
        ->addColumn('verification_code', 'string', [
            'limit' => 64,
            'null' => true,
            'after' => 'email_verified',
            'comment' => 'Código de verificación para el email'
        ])
        ->addColumn('verification_code_expires_at', 'timestamp', [
            'null' => true,
            'after' => 'verification_code',
            'comment' => 'Fecha de expiración del código de verificación'
        ])
        ->addColumn('account_status', 'enum', [
            'values' => ['pending', 'active', 'suspended', 'inactive'],
            'default' => 'pending',
            'after' => 'verification_code_expires_at',
            'comment' => 'Estado de la cuenta del usuario'
        ])
        ->addIndex(['verification_code'], ['unique' => false])
        ->addIndex(['email_verified'])
        ->addIndex(['account_status'])
        ->update();

        // Hacer campos opcionales para permitir registro solo con email
        $table->changeColumn('first_name', 'string', [
            'limit' => 100,
            'null' => true
        ])
        ->changeColumn('last_name', 'string', [
            'limit' => 100,
            'null' => true
        ])
        ->changeColumn('password', 'string', [
            'limit' => 255,
            'null' => true
        ])
        ->changeColumn('phone_number', 'string', [
            'limit' => 20,
            'null' => true
        ])
        ->update();
    }

    public function down(): void
    {
        $table = $this->table('users');

        // Revertir campos a requeridos
        $table->changeColumn('first_name', 'string', [
            'limit' => 100,
            'null' => false
        ])
        ->changeColumn('last_name', 'string', [
            'limit' => 100,
            'null' => false
        ])
        ->changeColumn('password', 'string', [
            'limit' => 255,
            'null' => false
        ])
        ->changeColumn('phone_number', 'string', [
            'limit' => 20,
            'null' => false
        ])
        ->update();

        // Eliminar campos de verificación
        $table->removeColumn('email_verified')
              ->removeColumn('verification_code')
              ->removeColumn('verification_code_expires_at')
              ->removeColumn('account_status')
              ->update();
    }
}