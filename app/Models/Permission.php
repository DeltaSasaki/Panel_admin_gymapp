<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    protected $fillable = [
        'code',
        'name',
        'module',
        'type',
        'description',
    ];

    /**
     * Roles assigned to this permission.
     */
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id');
    }

    /**
     * User overrides assigned to this permission.
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permission_id');
    }
}
