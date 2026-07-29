<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gym;

class SettingsController extends Controller
{
    /**
     * Check if user is authenticated and get current gym ID.
     */
    private function checkAuth()
    {
        if (!auth()->check()) {
            abort(401, 'No autenticado');
        }
        return auth()->user()->gym_id;
    }

    /**
     * Display settings page.
     */
    public function index()
    {
        $this->checkAuth();
        $user = auth()->user();
        $gym = Gym::find($user->gym_id);

        return view('configuracion.index', [
            'user' => $user,
            'gym' => $gym,
        ]);
    }

    /**
     * Update user font size preference in session.
     */
    public function updateFontSize(Request $request)
    {
        $this->checkAuth();
        $request->validate([
            'font_size' => 'required|in:small,normal,large,xlarge',
        ]);

        session(['font_size' => $request->font_size]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'font_size' => $request->font_size,
                'message' => '¡Preferencia de tamaño de letra guardada correctamente!'
            ]);
        }

        return redirect()->back()->with('success', '¡Preferencia de tamaño de letra actualizada con éxito!');
    }
}
