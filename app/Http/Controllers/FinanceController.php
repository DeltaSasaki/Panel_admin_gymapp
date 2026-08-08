<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPayment;
use App\Models\PromoCode;
use App\Models\GymPromotion;
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

        // Fetch Gym Promotions (Descuentos y paquetes de membresía)
        $gymPromosQuery = GymPromotion::with(['gym', 'plan']);
        if ($gymId !== 'all') {
            $gymPromosQuery->where('gym_id', $gymId);
        }
        $gymPromotions = $gymPromosQuery->orderBy('id', 'desc')->get();

        // Fetch pending verification payments (Binance, Pago Móvil, Transfers from Mobile App / API)
        // Only include payments for memberships that are still pending or overdue (requiring manual admin verification)
        $pendingVerificationPayments = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'user.profile'])
            ->whereHas('membership', function ($q) use ($gymId) {
                $q->whereIn('payment_status', ['pending', 'overdue']);
                if ($gymId !== 'all') {
                    $q->where('gym_id', $gymId);
                }
            })
            ->whereNotNull('reference_code')
            ->where('reference_code', '!=', '')
            ->orderBy('id', 'desc')
            ->get();

        return view('finanzas.index', compact('plans', 'memberships', 'clients', 'totalCollected', 'pendingAmount', 'promos', 'gymPromotions', 'pendingVerificationPayments'));
    }

    /**
     * Export Financial Balance and Transactions to CSV/Excel format.
     */
    public function exportExcel(Request $request)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $format = $request->query('format', 'csv'); // csv or xlsx

        $payments = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'receivedBy'])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->whereHas('membership', function ($sq) use ($gymId) {
                    $sq->where('gym_id', $gymId);
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $sales = \App\Models\ProductSale::with(['user.profile', 'soldBy'])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->orderBy('id', 'desc')
            ->get();

        $filename = 'Balance_Financiero_' . date('Ymd_His') . '.' . ($format === 'xlsx' ? 'xlsx' : 'csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($payments, $sales) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'ID Transaccion',
                'Tipo Concepto',
                'Fecha / Hora',
                'Cliente / Atleta',
                'Detalle / Plan / Producto',
                'Metodo de Pago',
                'Monto ($)',
                'Cajero / Atendido Por'
            ]);

            // Membership Payments
            foreach ($payments as $p) {
                $clientName = ($p->membership && $p->membership->user && $p->membership->user->profile)
                    ? $p->membership->user->profile->first_name . ' ' . $p->membership->user->profile->last_name
                    : 'Socio #' . ($p->membership->user_id ?? 'N/A');

                $planName = $p->membership->plan->name ?? 'Membresia';
                $cashier = $p->receivedBy->name ?? 'Sistema / Recepcion';

                fputcsv($file, [
                    'MEM-' . $p->id,
                    'Membresia',
                    $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d H:i') : 'N/A',
                    $clientName,
                    $planName,
                    strtoupper($p->payment_method ?? 'cash'),
                    number_format($p->amount, 2, '.', ''),
                    $cashier
                ]);
            }

            // POS Sales
            foreach ($sales as $s) {
                $clientName = ($s->user && $s->user->profile)
                    ? $s->user->profile->first_name . ' ' . $s->user->profile->last_name
                    : 'Cliente General POS';

                $cashier = $s->soldBy->name ?? 'Caja POS';

                fputcsv($file, [
                    'POS-' . $s->id,
                    'Venta Tienda POS',
                    $s->sale_date ? \Carbon\Carbon::parse($s->sale_date)->format('Y-m-d H:i') : $s->createdAt,
                    $clientName,
                    'Venta POS #' . $s->id,
                    strtoupper($s->payment_method ?? 'cash'),
                    number_format($s->total_amount, 2, '.', ''),
                    $cashier
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
     * Record advance payment (Abono) for client and calculate extra days added to membership.
     */
    public function recordAbono(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'user_membership_id' => 'required|exists:user_memberships,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,other',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            $membership = UserMembership::with(['plan', 'user.profile'])->findOrFail($request->user_membership_id);

            if (!$membership->plan) {
                $errMsg = 'La membresía seleccionada no tiene un plan asociado.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }

            $plan = $membership->plan;
            $durationDays = max(1, (int)$plan->duration_days);
            $dailyRate = (float)$plan->price / $durationDays;

            if ($dailyRate <= 0) {
                $errMsg = 'El plan de membresía actual no tiene una tarifa diaria válida para abonar.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }

            $user = $membership->user;
            $prevCredit = (float) ($user->credit_balance ?? 0);
            $payAmount = (float) $request->amount;
            $totalFunds = $payAmount + $prevCredit;

            $extraDays = (int) floor($totalFunds / $dailyRate);

            if ($extraDays < 1) {
                $newCredit = round($totalFunds, 2);
                $user->update(['credit_balance' => $newCredit]);

                $notes = "ABONO EN CREDITOS: Monto \${$payAmount} guardado en Saldo a Favor. Saldo total acumulado: \${$newCredit}. (Tarifa diaria: \$" . number_format($dailyRate, 2) . "/día).";
                
                $payment = MembershipPayment::create([
                    'membership_id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'amount' => $payAmount,
                    'payment_date' => Carbon::now(),
                    'payment_method' => $request->payment_method,
                    'reference_code' => $request->reference_number,
                    'received_by' => auth()->user()->id,
                    'currency' => $plan->currency ?? 'USD',
                    'notes' => $notes,
                ]);

                $msg = "Abono de \${$payAmount} guardado como Saldo a Favor. Crédito acumulado total: \${$newCredit}. Faltan \$" . number_format($dailyRate - $newCredit, 2) . " para 1 día extra.";
                
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'extra_days' => 0,
                        'credit_balance' => $newCredit,
                        'membership' => $membership->fresh(['user.profile', 'plan']),
                    ]);
                }

                return redirect()->back()->with('success', $msg);
            }

            // Calculate cost used for extra days & remaining new credit
            $costUsed = $extraDays * $dailyRate;
            $newCredit = max(0, round($totalFunds - $costUsed, 2));

            // Update user credit balance
            $user->update(['credit_balance' => $newCredit]);

            // Calculate new end date based on current end_date or now
            $oldData = $membership->toArray();
            $currentEndDate = Carbon::parse($membership->end_date);
            $baseDate = $currentEndDate->isPast() ? Carbon::now() : $currentEndDate;
            $newEndDate = $baseDate->copy()->addDays($extraDays);

            // Update membership end date and status
            $membership->update([
                'end_date' => $newEndDate,
                'status' => 'active',
                'payment_status' => 'paid',
            ]);

            // Record transaction
            $formattedRate = number_format($dailyRate, 2);
            $creditText = $newCredit > 0 ? " (Saldo a favor acumulado remanente: \${$newCredit})" : "";
            $notes = "ABONO ADELANTADO: Pago de \${$payAmount}" . ($prevCredit > 0 ? " + \${$prevCredit} crédito previo" : "") . " otorgó +{$extraDays} día(s) extra a \${$formattedRate}/día. Nueva vigencia hasta " . $newEndDate->format('d/m/Y') . "{$creditText}.";
            
            $payment = MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'amount' => $payAmount,
                'payment_date' => Carbon::now(),
                'payment_method' => $request->payment_method,
                'reference_code' => $request->reference_number,
                'received_by' => auth()->user()->id,
                'currency' => $plan->currency ?? 'USD',
                'notes' => $notes,
            ]);

            $userName = ($user && $user->profile) 
                ? $user->profile->first_name . ' ' . $user->profile->last_name 
                : ($user->email ?? 'Socio');

            AdminAuditLog::logAction(
                'TRANSACCION',
                'Abono por Adelantado',
                "Abono por Adelantado de \${$payment->amount} {$payment->currency} registrado para {$userName}. Vigencia extendida +{$extraDays} días hasta el {$newEndDate->format('d/m/Y')}. Saldo a favor sobrante: \${$newCredit}.",
                $oldData,
                $membership->fresh()->toArray(),
                $membership->gym_id
            );

            $msg = "¡Abono registrado con éxito! Se otorgaron +{$extraDays} día(s) extra a {$userName}. Nueva vigencia hasta " . $newEndDate->format('d/m/Y') . ($newCredit > 0 ? " (Queda \${$newCredit} en saldo a favor)." : ".");

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'extra_days' => $extraDays,
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                    'new_end_date_formatted' => $newEndDate->format('d/m/Y'),
                    'membership' => $membership->fresh(['user.profile', 'plan']),
                ]);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al registrar el abono: ' . $errorMessage;
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
     * Store new Gym Promotion (Paquetes y descuentos por meses seguidos).
     */
    public function storeGymPromotion(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'title' => 'required|string|max:150',
            'plan_id' => 'required|exists:membership_plans,id',
            'months_count' => 'required|integer|min:1|max:36',
            'discount_pct' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $errMsg = 'Debes seleccionar una sucursal específica para crear una promoción.';
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => $errMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
        }

        $plan = MembershipPlan::findOrFail($request->plan_id);
        $months = (int) $request->months_count;
        $discountPct = (float) $request->discount_pct;

        // Calculate regular price vs promotional price
        $regularTotalPrice = (float) $plan->price * $months;
        $promotionalPrice = max(0, round($regularTotalPrice * (1 - ($discountPct / 100)), 2));

        $promo = GymPromotion::create([
            'gym_id' => $gymId,
            'plan_id' => $plan->id,
            'title' => $request->title,
            'description' => $request->description,
            'months_count' => $months,
            'discount_pct' => $discountPct,
            'promotional_price' => $promotionalPrice,
            'valid_until' => $request->valid_until,
            'is_active' => 1,
        ]);

        AdminAuditLog::logAction(
            'CREACION',
            'Promoción de Gimnasio',
            "Promoción '{$promo->title}' ({$months} meses con {$discountPct}% OFF - Precio \${$promotionalPrice}) creada exitosamente.",
            null,
            $promo->toArray(),
            $gymId
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Promoción '{$promo->title}' creada exitosamente.",
                'promo' => $promo->load('plan')
            ]);
        }

        return redirect()->back()->with('success', "Promoción '{$promo->title}' creada exitosamente.");
    }

    /**
     * Toggle active status of a Gym Promotion.
     */
    public function toggleGymPromotion($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = GymPromotion::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $promo = $query->findOrFail($id);
        $oldState = $promo->toArray();
        $newStatus = $promo->is_active ? 0 : 1;
        $promo->update(['is_active' => $newStatus]);

        $actionLabel = $newStatus ? 'HABILITADO' : 'INHABILITADO';
        $descLabel = $newStatus ? 'activada' : 'desactivada';

        AdminAuditLog::logAction(
            $actionLabel,
            'Promoción de Gimnasio',
            "Promoción '{$promo->title}' {$descLabel} por el administrador.",
            $oldState,
            $promo->toArray(),
            $promo->gym_id
        );

        return response()->json([
            'success' => true,
            'message' => "Promoción '{$promo->title}' {$descLabel} exitosamente.",
            'is_active' => $newStatus
        ]);
    }

    /**
     * Delete Gym Promotion.
     */
    public function deleteGymPromotion($id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $query = GymPromotion::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $promo = $query->findOrFail($id);
        $oldState = $promo->toArray();
        $promoTitle = $promo->title;
        $promoGymId = $promo->gym_id;

        $promo->delete();

        AdminAuditLog::logAction(
            'ELIMINACION',
            'Promoción de Gimnasio',
            "Promoción '{$promoTitle}' eliminada por el administrador.",
            $oldState,
            null,
            $promoGymId
        );

        return response()->json([
            'success' => true,
            'message' => "Promoción '{$promoTitle}' eliminada exitosamente."
        ]);
    }

    /**
     * Approve a pending payment (Binance, Pago Móvil, Transfers) and activate/extend membership.
     */
    public function approvePendingPayment(Request $request, $id)
    {
        $this->checkAdmin();
        $payment = MembershipPayment::with(['membership.plan', 'membership.user.profile'])->findOrFail($id);
        $membership = $payment->membership;

        if (!$membership) {
            return redirect()->back()->withErrors(['error' => 'Membresía no encontrada para este pago.']);
        }

        $oldMembership = $membership->toArray();
        $oldPayment = $payment->toArray();

        $durationDays = $membership->plan ? ($membership->plan->duration_days ?: 30) : 30;

        // Calculate new start and end dates from today
        $startDate = Carbon::now()->toDateString();
        $endDate = Carbon::now()->addDays($durationDays)->toDateString();

        // Update membership status to paid and active
        $membership->update([
            'status' => 'active',
            'payment_status' => 'paid',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notes' => trim(($membership->notes ?? '') . " | Pago Ref: {$payment->reference_code} APROBADO por Admin el " . now()->format('d/m/Y H:i')),
        ]);

        // Update payment record
        $payment->update([
            'received_by' => auth()->id(),
            'payment_date' => now(),
            'notes' => trim(($payment->notes ?? '') . " [Aprobado por Admin #" . auth()->id() . "]"),
        ]);

        // Audit Log
        AdminAuditLog::logAction('UPDATE', 'user_memberships', $membership->id, $oldMembership, $membership->fresh()->toArray(), $membership->gym_id);
        AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $membership->gym_id);

        if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
            $userName = trim(($membership->user->profile->first_name ?? '') . ' ' . ($membership->user->profile->last_name ?? ''));
            activity()
                ->performedOn($membership)
                ->causedBy(auth()->user())
                ->log("Aprobación manual de pago {$payment->payment_method} (Ref: {$payment->reference_code}) para socio {$userName}. Membresía activada hasta {$endDate}");
        }

        $userName = trim(($membership->user->profile->first_name ?? '') . ' ' . ($membership->user->profile->last_name ?? ''));
        $msg = "¡Pago Ref: {$payment->reference_code} comprobado y APROBADO! La membresía de {$userName} fue activada exitosamente hasta el {$endDate}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Reject a pending payment (invalid transaction / reference code).
     */
    public function rejectPendingPayment(Request $request, $id)
    {
        $this->checkAdmin();
        $payment = MembershipPayment::with(['membership.user.profile'])->findOrFail($id);
        $membership = $payment->membership;

        $reason = $request->input('rejection_reason', 'Referencia de pago no verificada o rechazada por el administrador.');

        $oldPayment = $payment->toArray();
        $payment->update([
            'notes' => trim(($payment->notes ?? '') . " [RECHAZADO por Admin: {$reason}]"),
        ]);

        if ($membership && in_array($membership->payment_status, ['pending', 'overdue'])) {
            $membership->update([
                'payment_status' => 'overdue',
                'notes' => trim(($membership->notes ?? '') . " | Pago Ref: {$payment->reference_code} RECHAZADO: {$reason}"),
            ]);
        }

        AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $payment->gym_id ?? null);

        $msg = "El pago con referencia #{$payment->reference_code} ha sido marcado como RECHAZADO.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
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
