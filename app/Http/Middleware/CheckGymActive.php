<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckGymActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Superadmins bypass gym status restrictions
            if ($user->role !== 'superadmin') {
                if ($user->gym && !$user->gym->is_active) {
                    Auth::logout();
                    
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('login')->withErrors([
                        'email' => 'El acceso a esta sucursal ha sido suspendido temporalmente por pago vencido. Comunícate con soporte.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}
