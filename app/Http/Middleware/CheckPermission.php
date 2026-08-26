<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If no permissions specified, allow through
        if (empty($permissions)) {
            return $next($request);
        }

        // Support pipe or comma separated strings within elements (e.g. 'view|manage')
        $allPermissions = [];
        foreach ($permissions as $p) {
            foreach (preg_split('/[,|]/', $p) as $subP) {
                $trimmed = trim($subP);
                if ($trimmed !== '') {
                    $allPermissions[] = $trimmed;
                }
            }
        }

        // Check if user has ANY of the required permissions (OR logic for alternative permissions)
        $hasAccess = false;
        foreach ($allPermissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            $requiredList = implode(', ', $allPermissions);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No dispones de los permisos requeridos para realizar esta acción.',
                    'required_permissions' => $allPermissions
                ], 403);
            }

            abort(403, "No dispones de los permisos requeridos ({$requiredList}) para acceder a este módulo.");
        }

        return $next($request);
    }
}
