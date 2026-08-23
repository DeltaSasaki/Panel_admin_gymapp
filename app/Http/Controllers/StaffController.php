<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\AdminAuditLog;
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

        $query = Trainer::with([
            'user.profile',
            'gym',
            'assignedClients.user.profile',
            'routines'
        ]);

        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $trainers = $query->orderBy('first_name')->get();
        $gyms = \App\Models\Gym::where('is_active', 1)->get();

        return view('staff.index', compact('trainers', 'gyms'));
    }

    /**
     * Get full detailed profile for a trainer (AJAX modal).
     */
    public function showDetails($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Trainer::with([
            'user.profile',
            'gym',
            'assignedClients.user.profile',
            'routines'
        ]);

        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $trainer = $query->findOrFail($id);

        $assignedClients = $trainer->assignedClients->map(function ($assignment) {
            $u = $assignment->user;
            $p = $u ? $u->profile : null;
            return [
                'id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'full_name' => $p ? trim($p->first_name . ' ' . $p->last_name) : 'Usuario #' . $assignment->user_id,
                'email' => $u ? $u->email : '',
                'phone' => $p ? ($p->phone ?? 'Sin teléfono') : 'Sin teléfono',
                'dni' => $p ? ($p->dni ?? 'Sin DNI') : 'Sin DNI',
                'photo' => $p && $p->profile_photo ? asset($p->profile_photo) : null,
                'assigned_at' => $assignment->assigned_at ? Carbon::parse($assignment->assigned_at)->format('d/m/Y') : 'Fecha no reg.',
                'notes' => $assignment->notes ?? 'Sin notas específicas.',
            ];
        });

        $routines = $trainer->routines->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'difficulty' => $r->difficulty,
                'goal_type' => $r->goal_type,
                'duration_weeks' => $r->duration_weeks,
                'days_per_week' => $r->days_per_week,
                'is_active' => $r->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'trainer' => $trainer,
            'assigned_clients' => $assignedClients,
            'routines' => $routines,
        ]);
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
            'dni' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:200',
            'certification' => 'nullable|string|max:200',
            'experience_years' => 'nullable|integer|min:0',
            'hire_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'bio' => 'nullable|string',
            'max_clients' => 'nullable|integer|min:1',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder registrar un entrenador.']);
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'trainer_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/trainers'), $filename);
            $photoUrl = 'uploads/trainers/' . $filename;
        }

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
                'dni' => $request->dni,
                'phone' => $request->phone,
                'gender' => 'other',
                'profile_photo' => $photoUrl,
            ]);

            // Create Trainer Specialty Info
            $trainer = Trainer::create([
                'user_id' => $user->id,
                'gym_id' => $gymId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'specialty' => $request->specialty ?? 'Entrenador General',
                'certification' => $request->certification ?? 'Certificación Fitness',
                'experience_years' => $request->experience_years ?? 0,
                'photo_url' => $photoUrl,
                'bio' => $request->bio,
                'max_clients' => $request->max_clients ?? 20,
                'is_active' => 1,
                'hire_date' => $request->filled('hire_date') ? $request->hire_date : Carbon::today(),
                'salary' => $request->salary,
            ]);

            AdminAuditLog::logAction('INSERT', 'trainers', $user->id, null, ['email' => $request->email, 'name' => $request->first_name . ' ' . $request->last_name], $gymId);

            DB::commit();
            $message = 'Entrenador registrado exitosamente.';

            if ($request->ajax() || $request->wantsJson()) {
                $trainer->load(['user.profile', 'gym', 'assignedClients.user.profile', 'routines']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'trainer' => $trainer
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();

            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error al registrar entrenador: ' . $errorMessage;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorText], 422);
            }

            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        }
    }

    /**
     * Toggle trainer status.
     */
    public function toggleStatus($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();
        $query = Trainer::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $trainer = $query->findOrFail($id);
        $user = User::findOrFail($trainer->user_id);

        $newStatus = $trainer->is_active ? 0 : 1;
        $trainer->update(['is_active' => $newStatus]);
        $user->update(['is_active' => $newStatus]);

        $message = $newStatus 
            ? "Entrenador '{$trainer->first_name} {$trainer->last_name}' reactivado con éxito."
            : "Entrenador '{$trainer->first_name} {$trainer->last_name}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'trainer_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an existing trainer.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();
        $query = Trainer::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $trainer = $query->findOrFail($id);
        $user = User::findOrFail($trainer->user_id);
        $profile = UserProfile::where('user_id', $user->id)->first();

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'dni' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:200',
            'certification' => 'nullable|string|max:200',
            'experience_years' => 'nullable|integer|min:0',
            'hire_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'bio' => 'nullable|string',
            'max_clients' => 'nullable|integer|min:1',
        ]);

        $photoUrl = $trainer->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists on disk
            if ($trainer->photo_url && file_exists(public_path($trainer->photo_url))) {
                @unlink(public_path($trainer->photo_url));
            }
            $file = $request->file('photo');
            $filename = 'trainer_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/trainers'), $filename);
            $photoUrl = 'uploads/trainers/' . $filename;
        }

        try {
            DB::beginTransaction();

            // Update core User
            $userData = [
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userData['password_hash'] = Hash::make($request->password);
            }
            $user->update($userData);

            // Update Profile
            if ($profile) {
                $profile->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'dni' => $request->dni,
                    'phone' => $request->phone,
                    'profile_photo' => $photoUrl,
                ]);
            }

            // Update Trainer
            $trainer->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'specialty' => $request->specialty ?? 'Entrenador General',
                'certification' => $request->certification ?? 'Certificación Fitness',
                'experience_years' => $request->experience_years ?? 0,
                'hire_date' => $request->filled('hire_date') ? $request->hire_date : $trainer->hire_date,
                'photo_url' => $photoUrl,
                'bio' => $request->bio,
                'max_clients' => $request->max_clients ?? 20,
                'salary' => $request->salary,
            ]);

            DB::commit();
            $message = 'Datos del entrenador actualizados exitosamente.';

            if ($request->ajax() || $request->wantsJson()) {
                $trainer->load(['user.profile', 'gym', 'assignedClients.user.profile', 'routines']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'trainer' => $trainer
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al actualizar entrenador: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }
    }

    /**
     * Delete/destroy a trainer.
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();
        $query = Trainer::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $trainer = $query->findOrFail($id);
        $user = User::findOrFail($trainer->user_id);

        // Delete photo if exists
        if ($trainer->photo_url && file_exists(public_path($trainer->photo_url))) {
            @unlink(public_path($trainer->photo_url));
        }

        try {
            DB::beginTransaction();
            // Core User delete cascades profile deletion
            $trainer->delete();
            $user->delete();
            DB::commit();
            $message = 'Entrenador eliminado del staff correctamente.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'trainer_id' => $id
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al eliminar entrenador: ' . $e->getMessage();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
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
