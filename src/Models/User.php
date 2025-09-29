<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class User extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'roles',
        'email_verified',
        'verification_code',
        'verification_code_expires_at',
        'account_status'
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'verification_code_expires_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'roles' => 'array',
        'email_verified' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT);
        }
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function hasRole($role)
    {
        return in_array($role, $this->roles ?? []);
    }

    public function hasAnyRole($roles)
    {
        return !empty(array_intersect($roles, $this->roles ?? []));
    }

    public function assignRole($role)
    {
        $roles = $this->roles ?? [];
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->roles = $roles;
        }
    }

    public function removeRole($role)
    {
        $roles = $this->roles ?? [];
        $this->roles = array_values(array_filter($roles, function($r) use ($role) {
            return $r !== $role;
        }));
    }

    public function getRoles()
    {
        return $this->roles ?? [];
    }

    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->email;
    }

    /**
     * Generar código de verificación
     */
    public function generateVerificationCode(): string
    {
        $code = bin2hex(random_bytes(16));
        $this->verification_code = $code;
        $this->verification_code_expires_at = now()->addHours(24);
        return $code;
    }

    /**
     * Verificar si el código de verificación es válido
     */
    public function isVerificationCodeValid(string $code): bool
    {
        return $this->verification_code === $code &&
               $this->verification_code_expires_at &&
               $this->verification_code_expires_at->isFuture();
    }

    /**
     * Marcar email como verificado
     */
    public function markEmailAsVerified(): void
    {
        $this->email_verified = true;
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->account_status = 'active';
    }

    /**
     * Verificar si la cuenta está activa
     */
    public function isActive(): bool
    {
        return $this->account_status === 'active' && $this->email_verified;
    }

    /**
     * Verificar si la cuenta está pendiente de verificación
     */
    public function isPendingVerification(): bool
    {
        return $this->account_status === 'pending' || !$this->email_verified;
    }
}