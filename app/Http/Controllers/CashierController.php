<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cashier;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\AdminAuditLog;
use App\Models\MembershipPayment;
use App\Models\ProductSale;
use App\Models\Gym;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierController extends Controller
{
    /**
     * List all cashiers for the active gym.
     */
    public function index()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Cashier::with([
            'user.profile',
            'gym',
            'membershipPayments',
            'productSales'
        ]);

        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $cashiers = $query->orderBy('first_name')->get();
        $gyms = Gym::where('is_active', 1)->get();

        return view('cajeros.index', compact('cashiers', 'gyms'));
    }

    /**
     * Get full detailed profile for a cashier (AJAX modal).
     */
    public function showDetails($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Cashier::with([
            'user.profile',
            'gym',
            'membershipPayments.user.profile',
            'membershipPayments.membership',
            'productSales.client.profile'
        ]);

        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $cashier = $query->findOrFail($id);

        $recentPayments = $cashier->membershipPayments()
            ->with(['user.profile', 'membership'])
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $userProfile = $p->user ? $p->user->profile : null;
                return [
                    'id' => $p->id,
                    'client_name' => $userProfile ? ($userProfile->first_name . ' ' . $userProfile->last_name) : 'Cliente #' . $p->user_id,
                    'amount' => '$' . number_format($p->amount, 2),
                    'payment_method' => ucfirst($p->payment_method ?? 'Efectivo'),
                    'payment_date' => $p->payment_date ? Carbon::parse($p->payment_date)->format('d/m/Y H:i') : '',
                    'reference' => $p->reference_code ?? 'N/A',
                ];
            });

        $recentSales = $cashier->productSales()
            ->with(['client.profile'])
            ->orderBy('sale_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $clientProfile = $s->client ? $s->client->profile : null;
                return [
                    'id' => $s->id,
                    'client_name' => $clientProfile ? ($clientProfile->first_name . ' ' . $clientProfile->last_name) : 'Cliente General',
                    'total' => '$' . number_format($s->total_amount, 2),
                    'payment_method' => ucfirst($s->payment_method ?? 'Efectivo'),
                    'sale_date' => $s->sale_date ? Carbon::parse($s->sale_date)->format('d/m/Y H:i') : '',
                ];
            });

        $totalPaymentsSum = (float) $cashier->membershipPayments()->sum('amount');
        $totalSalesSum = (float) $cashier->productSales()->sum('total_amount');
        $totalCollected = $totalPaymentsSum + $totalSalesSum;

        return response()->json([
            'success' => true,
            'cashier' => $cashier,
            'recent_payments' => $recentPayments,
            'recent_sales' => $recentSales,
            'total_collected' => '$' . number_format($totalCollected, 2),
            'total_payments_count' => $cashier->membershipPayments()->count(),
            'total_sales_count' => $cashier->productSales()->count(),
        ]);
    }

    /**
     * Store a new cashier.
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
            'shift' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'notes' => 'nullable|string',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            if ($request->filled('gym_id')) {
                $gymId = $request->gym_id;
            } else {
                return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder registrar un cajero.']);
            }
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'cashier_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/cashiers'), $filename);
            $photoUrl = 'uploads/cashiers/' . $filename;
        }

        try {
            DB::beginTransaction();

            // Create core User
            $user = User::create([
                'gym_id' => $gymId,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'cajero',
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

            // Create Cashier record
            $cashier = Cashier::create([
                'user_id' => $user->id,
                'gym_id' => $gymId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'shift' => $request->shift ?? 'Mañana (06:00 - 14:00)',
                'hire_date' => $request->filled('hire_date') ? $request->hire_date : Carbon::today(),
                'salary' => $request->salary,
                'photo_url' => $photoUrl,
                'notes' => $request->notes,
                'is_active' => 1,
            ]);

            AdminAuditLog::logAction('INSERT', 'cashiers', $user->id, null, ['email' => $request->email, 'name' => $request->first_name . ' ' . $request->last_name], $gymId);

            DB::commit();
            $message = 'Cajero registrado exitosamente en el staff.';

            if ($request->ajax() || $request->wantsJson()) {
                $cashier->load(['user.profile', 'gym', 'membershipPayments', 'productSales']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cashier' => $cashier
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();

            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $userMsg = trim($matches[1]);
            } else {
                $userMsg = 'Error al registrar cajero: ' . $errorMessage;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $userMsg], 422);
            }

            return redirect()->back()->withInput()->withErrors(['error' => $userMsg]);
        }
    }

    /**
     * Update an existing cashier.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Cashier::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $cashier = $query->findOrFail($id);
        $user = User::findOrFail($cashier->user_id);
        $profile = UserProfile::where('user_id', $user->id)->first();

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'dni' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'shift' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'notes' => 'nullable|string',
        ]);

        $photoUrl = $cashier->photo_url;
        if ($request->hasFile('photo')) {
            if ($cashier->photo_url && file_exists(public_path($cashier->photo_url))) {
                @unlink(public_path($cashier->photo_url));
            }
            $file = $request->file('photo');
            $filename = 'cashier_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/cashiers'), $filename);
            $photoUrl = 'uploads/cashiers/' . $filename;
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

            // Update Cashier
            $cashier->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'shift' => $request->shift ?? 'Mañana (06:00 - 14:00)',
                'hire_date' => $request->filled('hire_date') ? $request->hire_date : $cashier->hire_date,
                'salary' => $request->salary,
                'photo_url' => $photoUrl,
                'notes' => $request->notes,
            ]);

            DB::commit();
            $message = 'Datos del cajero actualizados exitosamente.';

            if ($request->ajax() || $request->wantsJson()) {
                $cashier->load(['user.profile', 'gym', 'membershipPayments', 'productSales']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cashier' => $cashier
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al actualizar cajero: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
    }

    /**
     * Toggle active status for a cashier.
     */
    public function toggleStatus($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Cashier::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $cashier = $query->findOrFail($id);
        $user = User::findOrFail($cashier->user_id);

        $newStatus = $cashier->is_active ? 0 : 1;
        $cashier->is_active = $newStatus;
        $cashier->save();

        $user->is_active = $newStatus;
        $user->save();

        $statusLabel = $newStatus ? 'reactivado' : 'inhabilitado';
        $message = "Cajero {$cashier->first_name} {$cashier->last_name} {$statusLabel} exitosamente.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $newStatus,
                'cashier_id' => $id
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete a cashier permanently.
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = Cashier::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $cashier = $query->findOrFail($id);
        $user = User::findOrFail($cashier->user_id);

        if ($cashier->photo_url && file_exists(public_path($cashier->photo_url))) {
            @unlink(public_path($cashier->photo_url));
        }

        try {
            DB::beginTransaction();
            $cashier->delete();
            $user->delete();
            DB::commit();
            $message = 'Cajero eliminado del staff correctamente.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cashier_id' => $id
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al eliminar cajero: ' . $e->getMessage();

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
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar el personal de caja.');
        }
    }
}
