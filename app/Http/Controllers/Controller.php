<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Retrieve the active Gym ID context.
     * Supports tenant-switching for superadmins.
     */
    protected function getActiveGymId()
    {
        if (auth()->check() && auth()->user()->role === 'superadmin') {
            return session('superadmin_gym_id', 'all');
        }
        
        return auth()->check() ? auth()->user()->gym_id : null;
    }

    /**
     * Check granular permission for the current authenticated user.
     * Aborts with 403 Forbidden if not authorized.
     */
    protected function authorizePermission(string $permissionCode)
    {
        if (!auth()->check()) {
            abort(401, 'No autenticado.');
        }

        if (!auth()->user()->hasPermission($permissionCode)) {
            if (request()->ajax() || request()->wantsJson()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'No tienes los permisos requeridos para realizar esta acción [' . $permissionCode . ']. Contacta al administrador.'
                ], 403));
            }
            abort(403, 'No tienes permiso para acceder o realizar esta acción [' . $permissionCode . '].');
        }
    }
}
