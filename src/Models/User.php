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
        'roles'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'roles' => 'array'
    ];

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT);
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
        return $this->first_name . ' ' . $this->last_name;
    }
}