<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            
            // Check if the user is a trainer, admin, or superadmin
            if (in_array($user->role, ['trainer', 'admin', 'superadmin'])) {
                if (!$user->is_active) {
                    AdminAuditLog::logAction('LOGIN_FAILED', 'users', $user->id, null, ['reason' => 'Cuenta desactivada', 'email' => $credentials['email']], $user->gym_id, $user->id);
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Tu cuenta ha sido desactivada. Comunícate con soporte.',
                    ]);
                }
                
                $request->session()->regenerate();
                return redirect()->intended('dashboard');
            }

            // Reject members/clients from accessing the trainer admin panel
            AdminAuditLog::logAction('LOGIN_FAILED', 'users', $user->id, null, ['reason' => 'Intento de acceso por rol cliente', 'email' => $credentials['email']], $user->gym_id, $user->id);
            Auth::logout();
            return back()->withErrors([
                'email' => 'Acceso restringido. Este panel es exclusivo para entrenadores y administradores.',
            ]);
        }

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
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
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

        session(['superadmin_gym_id' => $request->gym_id]);

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
