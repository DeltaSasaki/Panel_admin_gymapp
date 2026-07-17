<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * List all gym trainers.
     */
    public function index()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();
        $trainers = Trainer::where('gym_id', $gymId)->with('user.profile')->get();

        return view('staff.index', compact('trainers'));
    }

    /**
     * Store a new trainer.
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:200',
            'certification' => 'nullable|string|max:200',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
        ]);

        $gymId = $this->getActiveGymId();

        try {
            DB::beginTransaction();

            // Create core User
            $user = User::create([
                'gym_id' => $gymId,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'trainer',
                'is_active' => 1,
                'email_verified' => 1,
            ]);

            // Create User Profile
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'gender' => 'other',
            ]);

            // Create Trainer Specialty Info
            Trainer::create([
                'user_id' => $user->id,
                'gym_id' => $gymId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'specialty' => $request->specialty ?? 'Entrenador General',
                'certification' => $request->certification ?? 'Certificación Fitness',
                'experience_years' => $request->experience_years ?? 0,
                'is_active' => 1,
                'hire_date' => Carbon::today(),
                'salary' => $request->salary,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Entrenador registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['email' => 'Error al registrar entrenador: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle trainer status.
     */
    public function toggleStatus($id)
    {
        $this->checkAdmin();
        $trainer = Trainer::where('gym_id', $this->getActiveGymId())->findOrFail($id);
        $user = User::findOrFail($trainer->user_id);

        $newStatus = $trainer->is_active ? 0 : 1;
        $trainer->update(['is_active' => $newStatus]);
        $user->update(['is_active' => $newStatus]);

        return redirect()->back()->with('success', 'Estado del entrenador actualizado.');
    }

    /**
     * Helper block for role protection.
     */
    private function checkAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar el personal.');
        }
    }
}
