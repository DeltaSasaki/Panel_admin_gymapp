<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\AdminAuditLog;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display the permissions matrix (roles, user overrides, exceptions).
     */
    public function index()
    {
        $this->authorizeAccess();

        $gymId = $this->getActiveGymId();

        // 1. Grouped catalog of all permissions
        $groupedPermissions = PermissionService::getAllGrouped();
        $allPermissionsCount = Permission::count();

        // 2. Default permissions for each role
        $rolePermissions = [
            'admin' => PermissionService::getRolePermissionIds('admin'),
            'cajero' => PermissionService::getRolePermissionIds('cajero'),
            'trainer' => PermissionService::getRolePermissionIds('trainer'),
        ];

        // 3. Staff users in current gym for individual overrides
        $staffQuery = User::whereIn('role', ['admin', 'cajero', 'trainer'])
            ->with(['profile', 'gym', 'permissionsOverride.permission']);

        if ($gymId !== 'all') {
            $staffQuery->where('gym_id', $gymId);
        }

        $staffUsers = $staffQuery->orderBy('role')->orderBy('email')->get();

        // 4. Users with at least one custom override
        $usersWithOverrides = $staffUsers->filter(function ($u) {
            return $u->permissionsOverride->isNotEmpty();
        });

        return view('admin.permisos.index', compact(
            'groupedPermissions',
            'allPermissionsCount',
            'rolePermissions',
            'staffUsers',
            'usersWithOverrides'
        ));
    }

    /**
     * Update default permissions for a role.
     */
    public function updateRole(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'role' => 'required|in:admin,cajero,trainer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = $request->role;
        $permissionIds = $request->input('permissions', []);

        $oldPermIds = PermissionService::getRolePermissionIds($role);

        PermissionService::updateRolePermissions($role, $permissionIds);

        AdminAuditLog::record(
            'UPDATE',
            'role_permissions',
            $role,
            ['permissions' => $oldPermIds],
            ['permissions' => $permissionIds],
            $this->getActiveGymId() === 'all' ? null : $this->getActiveGymId()
        );

        $roleLabel = match($role) {
            'admin' => 'Administrador',
            'cajero' => 'Cajero / Recepción',
            'trainer' => 'Entrenador / Coach',
            default => $role
        };

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permisos base para el rol [{$roleLabel}] actualizados exitosamente."
            ]);
        }

        return redirect()->back()->with('success', "Permisos base para el rol [{$roleLabel}] actualizados exitosamente.");
    }

    /**
     * AJAX endpoint: Get full permission breakdown and overrides for a specific user.
     */
    public function getUserPermissions(int $id)
    {
        $this->authorizeAccess();

        $user = $this->findScopedUser($id);
        $user->load(['profile', 'gym']);

        $detailedPermissions = PermissionService::getUserDetailedPermissions($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => trim(($user->profile->first_name ?? '') . ' ' . ($user->profile->last_name ?? '')) ?: $user->email,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => match($user->role) {
                    'superadmin' => 'SuperAdmin',
                    'admin' => 'Admin',
                    'cajero' => 'Cajero',
                    'trainer' => 'Coach',
                    default => $user->role
                },
                'gym_name' => $user->gym->name ?? 'Global',
                'photo' => $user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200',
            ],
            'permissions' => $detailedPermissions,
        ]);
    }

    /**
     * AJAX endpoint: Save custom overrides for a user.
     * Expects an array of { permission_id: number, override: 'grant'|'deny'|'default' }
     */
    public function updateUserPermissions(Request $request, int $id)
    {
        $this->authorizeAccess();

        $user = $this->findScopedUser($id);

        $request->validate([
            'overrides' => 'required|array',
            'overrides.*.permission_id' => 'required|integer|exists:permissions,id',
            'overrides.*.state' => 'required|in:granted,denied,default',
        ]);

        $changesApplied = 0;
        foreach ($request->overrides as $item) {
            $permId = (int) $item['permission_id'];
            $state = $item['state'];

            if ($state === 'default') {
                PermissionService::setUserPermissionOverride($user->id, $permId, null);
            } elseif ($state === 'granted') {
                PermissionService::setUserPermissionOverride($user->id, $permId, true);
            } elseif ($state === 'denied') {
                PermissionService::setUserPermissionOverride($user->id, $permId, false);
            }
            $changesApplied++;
        }

        AdminAuditLog::record(
            'UPDATE',
            'user_permissions',
            $user->id,
            null,
            ['user_id' => $user->id, 'overrides_count' => $changesApplied],
            $user->gym_id
        );

        $userName = trim(($user->profile->first_name ?? '') . ' ' . ($user->profile->last_name ?? '')) ?: $user->email;

        return response()->json([
            'success' => true,
            'message' => "Permisos personalizados para {$userName} guardados con éxito."
        ]);
    }

    /**
     * Reset all custom overrides for a user back to their role defaults.
     */
    public function resetUser(int $id)
    {
        $this->authorizeAccess();

        $user = $this->findScopedUser($id);
        PermissionService::resetUserOverrides($user->id);

        AdminAuditLog::record(
            'DELETE',
            'user_permissions',
            $user->id,
            null,
            ['user_id' => $user->id, 'action' => 'RESET_TO_ROLE_DEFAULT'],
            $user->gym_id
        );

        $userName = trim(($user->profile->first_name ?? '') . ' ' . ($user->profile->last_name ?? '')) ?: $user->email;

        return response()->json([
            'success' => true,
            'message' => "Se han restablecido los permisos de {$userName} a los valores predeterminados de su rol."
        ]);
    }

    /**
     * Helper to verify permission to manage permissions.
     */
    private function authorizeAccess(): void
    {
        if (!auth()->check()) {
            abort(401, 'No autenticado.');
        }

        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso restringido a administradores.');
        }
    }

    /**
     * Find user with tenant isolation.
     */
    private function findScopedUser(int $id): User
    {
        $gymId = $this->getActiveGymId();
        $query = User::where('id', $id);

        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        return $query->firstOrFail();
    }
}
