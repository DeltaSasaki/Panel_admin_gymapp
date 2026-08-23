<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\AdminAuditLog;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle the authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $throttleKey = Str::lower(trim($request->input('email'))) . '|' . $request->ip();

        // 1. Check if too many failed attempts (Max 5 attempts, lockout 60s)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Demasiados intentos fallidos de inicio de sesión. Por favor, intenta de nuevo en {$seconds} segundos.",
            ])->withInput($request->only('email'));
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            // Clear failed rate limit attempts on success
            RateLimiter::clear($throttleKey);

            $user = Auth::user();
            
            // Check if the user is a trainer, admin, superadmin, or cajero
            if (in_array($user->role, ['trainer', 'admin', 'superadmin', 'cajero'])) {
                if (!$user->is_active) {
                    AdminAuditLog::logAction('LOGIN_FAILED', 'users', $user->id, null, ['reason' => 'Cuenta desactivada', 'email' => $credentials['email']], $user->gym_id, $user->id);
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Tu cuenta ha sido desactivada. Comunícate con soporte.',
                    ]);
                }
                
                AdminAuditLog::logAction('LOGIN_SUCCESS', 'users', $user->id, null, ['email' => $user->email, 'role' => $user->role], $user->gym_id, $user->id);
                $request->session()->regenerate();
                return redirect()->intended('dashboard');
            }

            // Reject members/clients from accessing the trainer admin panel
            AdminAuditLog::logAction('LOGIN_FAILED', 'users', $user->id, null, ['reason' => 'Intento de acceso por rol cliente', 'email' => $credentials['email']], $user->gym_id, $user->id);
            Auth::logout();
            return back()->withErrors([
                'email' => 'Acceso restringido. Este panel es exclusivo para personal administrativo, cajeros y entrenadores.',
            ]);
        }

        // Increment failed attempt counter (lock for 60 seconds after 5 failures)
        RateLimiter::hit($throttleKey, 60);

        // Failed credentials
        $targetUser = User::where('email', $credentials['email'])->first();
        AdminAuditLog::logAction(
            'LOGIN_FAILED', 
            'users', 
            $targetUser ? $targetUser->id : null, 
            null, 
            ['reason' => 'Contraseña incorrecta o usuario no encontrado', 'email' => $credentials['email']], 
            $targetUser ? $targetUser->gym_id : null, 
            $targetUser ? $targetUser->id : null
        );

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput($request->only('email'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            AdminAuditLog::logAction('LOGOUT', 'users', $user->id, null, ['email' => $user->email], $user->gym_id, $user->id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Switch active gym context for superadmins.
     */
    public function switchGym(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'Acceso Denegado.'], 403);
            }
            abort(403, 'Acceso Denegado.');
        }

        $request->validate([
            'gym_id' => 'required|string',
        ]);

        if ($request->gym_id !== 'all') {
            $exists = \App\Models\Gym::where('id', $request->gym_id)->exists();
            if (!$exists) {
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => 'Gimnasio inválido.'], 422);
                }
                return redirect()->back()->withErrors(['gym_id' => 'Gimnasio inválido.']);
            }
        }

        $oldGym = session('superadmin_gym_id', 'all');
        session(['superadmin_gym_id' => $request->gym_id]);

        AdminAuditLog::logAction('SWITCH_GYM', 'gyms', $request->gym_id, ['old_gym_context' => $oldGym], ['new_gym_context' => $request->gym_id]);

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'gym_id' => $request->gym_id,
                'message' => 'Contexto de gimnasio cambiado con éxito.'
            ]);
        }

        return redirect()->back()->with('success', 'Contexto de gimnasio cambiado con éxito.');
    }
}
