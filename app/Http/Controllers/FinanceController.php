<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPayment;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\AdminAuditLog;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Display financial overview, membership plans and history.
     */
    public function index()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        // Get membership plans
        $plansQuery = MembershipPlan::query();
        if ($gymId !== 'all') {
            $plansQuery->where('gym_id', $gymId);
        }
        $plans = $plansQuery->get();

        // Get active & pending memberships
        $membershipsQuery = UserMembership::with(['user.profile', 'plan'])->orderBy('id', 'desc');
        if ($gymId !== 'all') {
            $membershipsQuery->where('gym_id', $gymId);
        }
        $memberships = $membershipsQuery->get();

        // Get clients to register new memberships
        $clientsQuery = User::where('role', 'member')->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $clients = $clientsQuery->get();

        // Financial stats
        $totalCollectedQuery = MembershipPayment::whereHas('membership', function ($q) use ($gymId) {
            if ($gymId !== 'all') {
                $q->where('gym_id', $gymId);
            }
        });
        $totalCollected = $totalCollectedQuery->sum('amount');

        $pendingAmountQuery = UserMembership::where('user_memberships.payment_status', 'pending')
            ->join('membership_plans', 'user_memberships.plan_id', '=', 'membership_plans.id');
        if ($gymId !== 'all') {
            $pendingAmountQuery->where('user_memberships.gym_id', $gymId);
        }
        $pendingAmount = $pendingAmountQuery->sum('membership_plans.price');

        // Fetch promo codes
        $promosQuery = PromoCode::with('gym');
        if ($gymId !== 'all') {
            $promosQuery->where(function($q) use ($gymId) {
                $q->where('gym_id', $gymId)->orWhereNull('gym_id');
            });
        }
        $promos = $promosQuery->orderBy('id', 'desc')->get();

        return view('finanzas.index', compact('plans', 'memberships', 'clients', 'totalCollected', 'pendingAmount', 'promos'));
    }

    /**
     * Store new membership plan.
     */
    public function storePlan(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $errMsg = 'Debes seleccionar una sucursal específica para poder crear un plan de membresía.';
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
        }

        $plan = MembershipPlan::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'duration_days' => $request->duration_days,
            'price' => $request->price,
            'currency' => $request->currency,
            'includes_trainer' => $request->has('includes_trainer') ? 1 : 0,
            'is_active' => 1,
        ]);

        AdminAuditLog::logAction(
            'CREACION',
            'Plan de Membresía',
            "Plan '{$plan->name}' ({$plan->duration_days} días - \${$plan->price} {$plan->currency}) creado exitosamente.",
            null,
            $plan->toArray(),
            $gymId
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Plan de membresía '{$plan->name}' creado exitosamente.",
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', 'Plan de membresía creado exitosamente.');
    }

    /**
     * Update existing membership plan.
     */
    public function updatePlan(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
        ]);

        $gymId = $this->getActiveGymId();
        $query = MembershipPlan::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }
        $plan = $query->findOrFail($id);
        $oldData = $plan->toArray();

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'duration_days' => $request->duration_days,
            'price' => $request->price,
            'currency' => $request->currency,
            'includes_trainer' => $request->has('includes_trainer') ? 1 : 0,
        ]);

        AdminAuditLog::logAction(
            'ACTUALIZACION',
            'Plan de Membresía',
            "Plan '{$plan->name}' actualizado exitosamente.",
            $oldData,
            $plan->toArray(),
            $plan->gym_id
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Plan de membresía '{$plan->name}' actualizado exitosamente.",
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', 'Plan de membresía actualizado exitosamente.');
    }

    /**
     * Toggle active status of a membership plan.
     */
    public function togglePlan(Request $request, $id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = MembershipPlan::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $plan = $query->findOrFail($id);
        $oldState = $plan->toArray();
        $newStatus = $plan->is_active ? 0 : 1;
        $plan->update(['is_active' => $newStatus]);

        $actionLabel = $newStatus ? 'HABILITADO' : 'INHABILITADO';
        $descLabel = $newStatus ? 'activado' : 'desactivado';

        AdminAuditLog::logAction(
            $actionLabel,
            'Plan de Membresía',
            "Plan '{$plan->name}' {$descLabel} por el administrador.",
            $oldState,
            $plan->toArray(),
            $plan->gym_id
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => "Estado del plan '{$plan->name}' actualizado a " . ($newStatus ? 'Activo' : 'Inactivo') . "."
            ]);
        }

        return redirect()->back()->with('success', 'Estado del plan de membresía actualizado.');
    }

    /**
     * Record payment for user membership.
     */
    public function recordPayment(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'user_membership_id' => 'required|exists:user_memberships,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,other',
            'reference_number' => 'nullable|string|max:100',
            'promo_code' => 'nullable|string',
        ]);

        $gymId = $this->getActiveGymId();

        // Find promo code if provided
        $promoId = null;
        if ($request->filled('promo_code')) {
            $promo = PromoCode::where('code', $request->promo_code)
                ->where('is_active', 1)
                ->where(function($q) use ($gymId) {
                    if ($gymId !== 'all') {
                        $q->where('gym_id', $gymId)->orWhereNull('gym_id');
                    }
                })
                ->first();
            if (!$promo) {
                $errMsg = 'El código promocional no es válido o ya expiró.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }
            $promoId = $promo->id;
        }

        try {
            $membership = UserMembership::findOrFail($request->user_membership_id);

            // Record payment
            $payment = MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'promo_code_id' => $promoId,
                'amount' => $request->amount,
                'payment_date' => Carbon::now(),
                'payment_method' => $request->payment_method,
                'reference_code' => $request->reference_number,
                'received_by' => auth()->user()->id,
                'currency' => $membership->plan->currency ?? 'USD',
            ]);

            // Update membership status
            $oldData = $membership->toArray();
            $membership->update([
                'payment_status' => 'paid',
                'status' => 'active',
            ]);

            $userName = ($membership->user && $membership->user->profile) 
                ? $membership->user->profile->first_name . ' ' . $membership->user->profile->last_name 
                : ($membership->user->email ?? 'Socio');

            AdminAuditLog::logAction(
                'TRANSACCION',
                'Pago de Membresía',
                "Pago de \${$payment->amount} {$payment->currency} registrado para el socio {$userName} (Método: {$request->payment_method}).",
                $oldData,
                $membership->toArray(),
                $membership->gym_id
            );

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "Pago de \${$payment->amount} registrado y membresía activada con éxito.",
                    'membership' => $membership
                ]);
            }

            return redirect()->back()->with('success', 'Pago registrado y membresía activada con éxito.');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al registrar el pago: ' . $errorMessage;
            }
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errorText], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            $errorText = 'Error inesperado: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errorText], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        }
    }

    /**
     * Renew/Create user membership.
     */
    public function renewMembership(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'required|date',
        ]);

        $gymId = $this->getActiveGymId();
        $user = User::findOrFail($request->user_id);
        $targetGymId = ($gymId === 'all') ? $user->gym_id : $gymId;

        $plan = MembershipPlan::findOrFail($request->plan_id);

        // Deactivate previous active memberships
        UserMembership::where('user_id', $request->user_id)->update(['status' => 'cancelled']);

        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($plan->duration_days);

        try {
            $membership = UserMembership::create([
                'user_id' => $request->user_id,
                'gym_id' => $targetGymId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => 'pending',
            ]);

            $userName = ($user->profile) 
                ? $user->profile->first_name . ' ' . $user->profile->last_name 
                : $user->email;

            AdminAuditLog::logAction(
                'CREACION',
                'Asignación de Membresía',
                "Membresía '{$plan->name}' asignada al socio {$userName} (Vigencia: {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}).",
                null,
                $membership->toArray(),
                $targetGymId
            );

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "Nueva membresía asignada al socio {$userName}. Registra el pago para activarla.",
                    'membership' => $membership
                ]);
            }

            return redirect()->back()->with('success', 'Nueva membresía asignada. Registra el pago para activarla.');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al asignar membresía: ' . $errorMessage;
            }
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errorText], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            $errorText = 'Error inesperado: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errorText], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        }
    }

    /**
     * Store new promo code.
     */
    public function storePromoCode(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $gymId = $this->getActiveGymId();
        
        $targetGymId = $gymId;
        if ($gymId === 'all') {
            if (auth()->user()->role === 'superadmin') {
                $targetGymId = null; // Global promo code
            } else {
                $errMsg = 'Debes seleccionar una sucursal específica para crear un código promocional.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }
        }

        $promo = PromoCode::create([
            'gym_id' => $targetGymId,
            'code' => strtoupper($request->code),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'max_uses' => $request->max_uses,
            'current_uses' => 0,
            'is_active' => 1,
        ]);

        $discountText = ($promo->discount_type === 'percentage') ? "{$promo->discount_value}%" : "\${$promo->discount_value}";
        AdminAuditLog::logAction(
            'CREACION',
            'Cupón Promocional',
            "Cupón promocional '{$promo->code}' ({$discountText} descuento) creado exitosamente.",
            null,
            $promo->toArray(),
            $targetGymId
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Código promocional '{$promo->code}' creado exitosamente.",
                'promo' => $promo
            ]);
        }

        return redirect()->back()->with('success', 'Código promocional creado exitosamente.');
    }

    /**
     * Toggle active status of a promo code.
     */
    public function togglePromoCode($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = PromoCode::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $promo = $query->findOrFail($id);
        $oldState = $promo->toArray();
        $newStatus = $promo->is_active ? 0 : 1;
        $promo->update(['is_active' => $newStatus]);

        $actionLabel = $newStatus ? 'HABILITADO' : 'INHABILITADO';
        $descLabel = $newStatus ? 'activado' : 'desactivado';

        AdminAuditLog::logAction(
            $actionLabel,
            'Cupón Promocional',
            "Cupón promocional '{$promo->code}' {$descLabel} por el administrador.",
            $oldState,
            $promo->toArray(),
            $promo->gym_id
        );

        if (request()->wantsJson() || request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => "Estado del código promocional '{$promo->code}' actualizado a " . ($newStatus ? 'Activo' : 'Inactivo') . "."
            ]);
        }

        return redirect()->back()->with('success', 'Estado del código promocional actualizado.');
    }

    /**
     * AJAX validation API endpoint for applying coupons.
     */
    public function validatePromo(Request $request)
    {
        $gymId = $this->getActiveGymId();
        $code = strtoupper($request->query('code'));

        $promo = PromoCode::where('code', $code)
            ->where('is_active', 1)
            ->where(function($q) use ($gymId) {
                if ($gymId !== 'all') {
                    $q->where('gym_id', $gymId)->orWhereNull('gym_id');
                }
            })
            ->first();

        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Código no válido o inactivo.']);
        }

        // Date check
        $now = Carbon::now();
        if ($promo->valid_from && Carbon::parse($promo->valid_from)->isFuture()) {
            return response()->json(['valid' => false, 'message' => 'Esta promoción aún no inicia.']);
        }
        if ($promo->valid_until && Carbon::parse($promo->valid_until)->isPast()) {
            return response()->json(['valid' => false, 'message' => 'Esta promoción ha expirado.']);
        }

        // Uses check
        if ($promo->max_uses && $promo->current_uses >= $promo->max_uses) {
            return response()->json(['valid' => false, 'message' => 'Esta promoción ya alcanzó su límite máximo de usos.']);
        }

        return response()->json([
            'valid' => true,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float)$promo->discount_value,
            'id' => $promo->id,
        ]);
    }

    /**
     * Helper block for role protection.
     */
    private function checkAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar las finanzas.');
        }
    }
}
