<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * In-memory request cache for role permissions.
     */
    protected static array $rolePermissionsCache = [];

    /**
     * In-memory request cache for user permission overrides.
     */
    protected static array $userPermissionsCache = [];

    /**
     * Flush all static in-memory caches.
     */
    public static function flushCache(): void
    {
        self::$rolePermissionsCache = [];
        self::$userPermissionsCache = [];
    }

    /**
     * Check if a user has a specific permission code.
     */
    public static function userHasPermission(User $user, string $permissionCode): bool
    {
        // Superadmin has absolute bypass for everything
        if ($user->role === 'superadmin') {
            return true;
        }

        $userId = $user->id;

        // 1. Load user overrides from static cache
        if (!isset(self::$userPermissionsCache[$userId])) {
            self::$userPermissionsCache[$userId] = DB::table('user_permissions')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $userId)
                ->pluck('user_permissions.is_granted', 'permissions.code')
                ->toArray();
        }

        // If user has an explicit custom override (1 = granted, 0 = denied)
        if (array_key_exists($permissionCode, self::$userPermissionsCache[$userId])) {
            return (bool) self::$userPermissionsCache[$userId][$permissionCode];
        }

        // 2. If no user override, check base role permissions
        $role = $user->role;
        if (!isset(self::$rolePermissionsCache[$role])) {
            self::$rolePermissionsCache[$role] = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role', $role)
                ->pluck('permissions.code')
                ->flip()
                ->toArray();
        }

        return isset(self::$rolePermissionsCache[$role][$permissionCode]);
    }

    /**
     * Get all catalog permissions grouped by module.
     */
    public static function getAllGrouped(): array
    {
        $permissions = Permission::orderBy('module')->orderBy('id')->get();
        $grouped = [];

        foreach ($permissions as $perm) {
            $grouped[$perm->module][] = $perm;
        }

        return $grouped;
    }

    /**
     * Get list of permission IDs enabled for a given role.
     */
    public static function getRolePermissionIds(string $role): array
    {
        return DB::table('role_permissions')
            ->where('role', $role)
            ->pluck('permission_id')
            ->toArray();
    }

    /**
     * Sync permissions for a role.
     */
    public static function updateRolePermissions(string $role, array $permissionIds): void
    {
        DB::transaction(function () use ($role, $permissionIds) {
            DB::table('role_permissions')->where('role', $role)->delete();

            $now = now();
            $records = [];
            foreach ($permissionIds as $permId) {
                $records[] = [
                    'role' => $role,
                    'permission_id' => (int) $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                DB::table('role_permissions')->insert($records);
            }
        });

        // Clear static cache
        unset(self::$rolePermissionsCache[$role]);
    }

    /**
     * Get a comprehensive breakdown of all permissions for a user:
     * - Status: 'inherited_granted', 'inherited_denied', 'custom_granted', 'custom_denied'
     */
    public static function getUserDetailedPermissions(User $user): array
    {
        $allPermissions = Permission::orderBy('module')->orderBy('id')->get();
        $rolePermIds = self::getRolePermissionIds($user->role);
        $userOverrides = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->pluck('is_granted', 'permission_id')
            ->toArray();

        $result = [];

        foreach ($allPermissions as $perm) {
            $roleHas = in_array($perm->id, $rolePermIds);
            $hasOverride = array_key_exists($perm->id, $userOverrides);
            
            $status = 'inherited_denied';
            $effective = false;

            if ($user->role === 'superadmin') {
                $status = 'superadmin';
                $effective = true;
            } elseif ($hasOverride) {
                if ($userOverrides[$perm->id]) {
                    $status = 'custom_granted';
                    $effective = true;
                } else {
                    $status = 'custom_denied';
                    $effective = false;
                }
            } else {
                if ($roleHas) {
                    $status = 'inherited_granted';
                    $effective = true;
                } else {
                    $status = 'inherited_denied';
                    $effective = false;
                }
            }

            $result[] = [
                'id' => $perm->id,
                'code' => $perm->code,
                'name' => $perm->name,
                'module' => $perm->module,
                'type' => $perm->type,
                'description' => $perm->description,
                'role_default' => $roleHas,
                'has_override' => $hasOverride,
                'override_value' => $hasOverride ? (bool) $userOverrides[$perm->id] : null,
                'status' => $status,
                'effective' => $effective,
            ];
        }

        return $result;
    }

    /**
     * Set a user permission override:
     * - $isGranted = 1 (Grant)
     * - $isGranted = 0 (Deny)
     * - $isGranted = null (Reset to role default)
     */
    public static function setUserPermissionOverride(int $userId, int $permissionId, ?bool $isGranted): void
    {
        if ($isGranted === null) {
            DB::table('user_permissions')
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->delete();
        } else {
            DB::table('user_permissions')->updateOrInsert(
                ['user_id' => $userId, 'permission_id' => $permissionId],
                [
                    'is_granted' => $isGranted ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        unset(self::$userPermissionsCache[$userId]);
    }

    /**
     * Reset all overrides for a user.
     */
    public static function resetUserOverrides(int $userId): void
    {
        DB::table('user_permissions')->where('user_id', $userId)->delete();
        unset(self::$userPermissionsCache[$userId]);
    }
}
