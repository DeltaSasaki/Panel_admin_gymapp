<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPayment;
use App\Models\UserCreditLog;
use App\Models\PromoCode;
use App\Models\GymPromotion;
use App\Models\User;
use App\Models\Notification;
use App\Models\Gym;
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

        // Get all memberships for audit & transaction history
        $membershipsQuery = UserMembership::with(['user.profile', 'plan'])->orderBy('id', 'desc');
        if ($gymId !== 'all') {
            $membershipsQuery->where('gym_id', $gymId);
        }
        $memberships = $membershipsQuery->get();

        // Get ONLY active memberships for assignment/abono modals
        $activeMembershipsQuery = UserMembership::where('status', 'active')->with(['user.profile', 'plan'])->orderBy('id', 'desc');
        if ($gymId !== 'all') {
            $activeMembershipsQuery->where('gym_id', $gymId);
        }
        $activeMemberships = $activeMembershipsQuery->get();

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

        // Only ACTIVE memberships with pending payment count towards pending collection stats
        $pendingAmountQuery = UserMembership::where('user_memberships.status', 'active')
            ->where('user_memberships.payment_status', 'pending')
            ->join('membership_plans', 'user_memberships.plan_id', '=', 'membership_plans.id');
        if ($gymId !== 'all') {
            $pendingAmountQuery->where('user_memberships.gym_id', $gymId);
        }
        $pendingAmount = $pendingAmountQuery->sum('membership_plans.price');

        // Fetch promo codes
        $promosQuery = PromoCode::with('gym');
        if ($gymId !== 'all') {
            $promosQuery->where(function ($q) use ($gymId) {
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

        // Fetch pending verification payments (Binance, Pago Móvil, Transfers from Mobile App / API, Abonos & Memberships)
        $pendingVerificationPayments = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'user.profile'])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where(function ($sq) use ($gymId) {
                    $sq->whereHas('membership', function ($mq) use ($gymId) {
                        $mq->where('gym_id', $gymId);
                    })->orWhereHas('user', function ($uq) use ($gymId) {
                        $uq->where('gym_id', $gymId);
                    });
                });
            })
            ->where(function ($q) {
                $q->where('notes', 'LIKE', '%[TOPUP_PENDIENTE]%')
                    ->orWhere('notes', 'LIKE', '%[PAGO_MOVIL_PENDIENTE]%')
                    ->orWhere('notes', 'LIKE', '%[PENDIENTE%')
                    ->orWhereNull('received_by')
                    ->orWhereHas('membership', function ($mq) {
                        $mq->whereIn('payment_status', ['pending', 'overdue']);
                    });
            })
            ->whereNotNull('reference_code')
            ->where('reference_code', '!=', '')
            ->orderBy('id', 'desc')
            ->get();

        // Fetch Abono & Saldo a Favor Logs
        $abonoLogsQuery = UserCreditLog::with(['user.profile', 'membership.plan', 'receiver.profile', 'payment'])->orderBy('id', 'desc');
        if ($gymId !== 'all') {
            $abonoLogsQuery->where('gym_id', $gymId);
        }
        $abonoLogs = $abonoLogsQuery->get();

        $totalAbonosAmount = $abonoLogs->where('type', 'abono_payment')->sum('amount');
        $totalDaysAwarded = $abonoLogs->sum('days_added');
        $totalCirculatingCredit = User::where('role', 'member')
            ->when($gymId !== 'all', fn($q) => $q->where('gym_id', $gymId))
            ->sum('credit_balance');

        return view('finanzas.index', compact(
            'plans',
            'memberships',
            'activeMemberships',
            'clients',
            'totalCollected',
            'pendingAmount',
            'promos',
            'gymPromotions',
            'pendingVerificationPayments',
            'abonoLogs',
            'totalAbonosAmount',
            'totalDaysAwarded',
            'totalCirculatingCredit'
        ));
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
        $this->checkCashierOrAdmin();
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
                ->where(function ($q) use ($gymId) {
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
            $membership = UserMembership::with('plan')->findOrFail($request->user_membership_id);

            // If the selected membership was cancelled, redirect to the user's active membership
            if ($membership->status === 'cancelled') {
                $activeMem = UserMembership::where('user_id', $membership->user_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->first();
                if ($activeMem) {
                    $membership = $activeMem;
                }
            }

            $currentRate = \App\Services\ExchangeRateService::getCurrentRate($membership->gym_id);

            // Record payment
            $payment = MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'promo_code_id' => $promoId,
                'amount' => $request->amount,
                'amount_ves' => round((float)$request->amount * $currentRate, 2),
                'exchange_rate' => $currentRate,
                'payment_currency' => 'USD',
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
        $this->checkCashierOrAdmin();
        $request->validate([
            'user_membership_id' => 'required|exists:user_memberships,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,other',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            $membership = UserMembership::with(['plan', 'user.profile'])->findOrFail($request->user_membership_id);

            // If the selected membership was cancelled, link to the user's active membership
            if ($membership->status === 'cancelled') {
                $activeMem = UserMembership::where('user_id', $membership->user_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->first();
                if ($activeMem) {
                    $membership = $activeMem;
                }
            }

            if (!$membership->plan) {
                $errMsg = 'La membresía seleccionada no tiene un plan asociado.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }

            $plan = $membership->plan;
            $planPrice = (float) $plan->price;
            $planDays = max(1, (int) ($plan->duration_days ?: 30));

            if ($planPrice <= 0) {
                $errMsg = 'El plan de membresía actual no tiene un costo válido configurado.';
                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['error' => $errMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errMsg]);
            }

            $user = $membership->user;
            $prevCredit = (float) ($user->credit_balance ?? 0);
            $payAmount = (float) $request->amount;
            $totalFunds = round($payAmount + $prevCredit, 2);

            $receiverId = auth()->check() ? auth()->user()->id : null;
            $source = $request->input('source', $receiverId ? 'admin_panel' : 'mobile_app');

            // Calculate how many full plan cycles (periods) can be covered by total funds
            $fullPeriods = (int) floor($totalFunds / $planPrice);

            // Case A: Funds are insufficient to cover a full plan period ($totalFunds < $planPrice)
            if ($fullPeriods < 1) {
                $newCredit = $totalFunds;
                $user->update(['credit_balance' => $newCredit]);

                $missingAmount = round($planPrice - $newCredit, 2);
                $notes = "ABONO EN SALDO A FAVOR: Recarga de \${$payAmount}" . ($prevCredit > 0 ? " (+ \${$prevCredit} previo)" : "") . ". Saldo acumulado: \${$newCredit} / \${$planPrice}. Faltan \${$missingAmount} para completar el plan '{$plan->name}'.";

                $currentRate = \App\Services\ExchangeRateService::getCurrentRate($membership->gym_id);
                $payment = MembershipPayment::create([
                    'membership_id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'amount' => $payAmount,
                    'amount_ves' => round((float)$payAmount * $currentRate, 2),
                    'exchange_rate' => $currentRate,
                    'payment_currency' => 'USD',
                    'payment_date' => Carbon::now(),
                    'payment_method' => $request->payment_method,
                    'reference_code' => $request->reference_number,
                    'received_by' => $receiverId,
                    'currency' => $plan->currency ?? 'USD',
                    'notes' => $notes,
                ]);

                $creditLog = UserCreditLog::create([
                    'gym_id' => $membership->gym_id,
                    'user_id' => $membership->user_id,
                    'membership_id' => $membership->id,
                    'payment_id' => $payment->id,
                    'received_by' => $receiverId,
                    'source' => $source,
                    'type' => 'abono_payment',
                    'amount' => $payAmount,
                    'payment_method' => $request->payment_method,
                    'reference_code' => $request->reference_number,
                    'daily_rate' => $planPrice,
                    'previous_credit' => $prevCredit,
                    'days_added' => 0,
                    'credit_used' => 0.00,
                    'resulting_credit' => $newCredit,
                    'notes' => $notes,
                ]);
                $creditLog->load(['user.profile', 'membership.plan', 'receiver.profile', 'payment']);

                $msg = "Abono de \${$payAmount} guardado en Saldo a Favor. Total acumulado: \${$newCredit}. Faltan \${$missingAmount} para renovar el plan ({$plan->name} - \${$planPrice}).";

                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'extra_days' => 0,
                        'full_periods' => 0,
                        'credit_balance' => $newCredit,
                        'plan_price' => $planPrice,
                        'missing_amount' => $missingAmount,
                        'log' => $creditLog,
                        'membership' => $membership->fresh(['user.profile', 'plan']),
                    ]);
                }

                return redirect()->back()->with('success', $msg);
            }

            // Case B: Funds are enough to cover 1 or more full periods ($totalFunds >= $planPrice)
            $costUsed = round($fullPeriods * $planPrice, 2);
            $daysToAdd = $fullPeriods * $planDays;
            $newCredit = max(0, round($totalFunds - $costUsed, 2));

            // Update user credit balance
            $user->update(['credit_balance' => $newCredit]);

            // Calculate new end date based on current end_date or now
            $oldData = $membership->toArray();
            $currentEndDate = Carbon::parse($membership->end_date);
            $baseDate = $currentEndDate->isPast() ? Carbon::now() : $currentEndDate;
            $newEndDate = $baseDate->copy()->addDays($daysToAdd);

            // Update membership end date and status
            $membership->update([
                'end_date' => $newEndDate,
                'status' => 'active',
                'payment_status' => 'paid',
            ]);

            $creditText = $newCredit > 0 ? " (Saldo a favor restante: \${$newCredit})" : "";
            $notes = "RENOVACIÓN POR ABONO COMPLETO: Pago de \${$payAmount}" . ($prevCredit > 0 ? " + \${$prevCredit} saldo acumulado previo" : "") . " cubrió el costo del plan '{$plan->name}' (\${$planPrice}). Se otorgaron +{$daysToAdd} días ({$fullPeriods} mes(es) / período(s)). Nueva vigencia hasta " . $newEndDate->format('d/m/Y') . "{$creditText}.";

            $currentRate = \App\Services\ExchangeRateService::getCurrentRate($membership->gym_id);
            $payment = MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'amount' => $payAmount,
                'amount_ves' => round((float)$payAmount * $currentRate, 2),
                'exchange_rate' => $currentRate,
                'payment_currency' => 'USD',
                'payment_date' => Carbon::now(),
                'payment_method' => $request->payment_method,
                'reference_code' => $request->reference_number,
                'received_by' => $receiverId,
                'currency' => $plan->currency ?? 'USD',
                'notes' => $notes,
            ]);

            $creditLog = UserCreditLog::create([
                'gym_id' => $membership->gym_id,
                'user_id' => $membership->user_id,
                'membership_id' => $membership->id,
                'payment_id' => $payment->id,
                'received_by' => $receiverId,
                'source' => $source,
                'type' => 'abono_payment',
                'amount' => $payAmount,
                'payment_method' => $request->payment_method,
                'reference_code' => $request->reference_number,
                'daily_rate' => $planPrice,
                'previous_credit' => $prevCredit,
                'days_added' => $daysToAdd,
                'credit_used' => $costUsed,
                'resulting_credit' => $newCredit,
                'notes' => $notes,
            ]);
            $creditLog->load(['user.profile', 'membership.plan', 'receiver.profile', 'payment']);

            AdminAuditLog::logAction(
                'TRANSACCION',
                'Abono Plan Completo',
                "Abono de \${$payAmount} completó el plan '{$plan->name}' para {$user->email} (+{$daysToAdd} días hasta " . $newEndDate->format('d/m/Y') . ").",
                $oldData,
                $membership->toArray(),
                $membership->gym_id
            );

            $periodText = $fullPeriods > 1 ? "{$fullPeriods} meses / períodos" : "1 mes / período completo (+{$daysToAdd} días)";
            $msg = "¡Abono de \${$payAmount} registrado! Se completó el costo del plan ({$plan->name} - \${$planPrice}). Se renovó {$periodText} hasta el " . $newEndDate->format('d/m/Y') . "." . ($newCredit > 0 ? " Saldo restante a favor: \${$newCredit}" : "");

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'extra_days' => $daysToAdd,
                    'full_periods' => $fullPeriods,
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                    'new_end_date_formatted' => $newEndDate->format('d/m/Y'),
                    'credit_balance' => $newCredit,
                    'log' => $creditLog,
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
        $this->checkCashierOrAdmin();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'required|date',
            'payment_method' => 'nullable|in:cash,card,transfer,other',
            'reference_number' => 'nullable|string|max:100',
            'use_credit' => 'nullable|boolean',
        ]);

        $gymId = $this->getActiveGymId();
        $user = User::findOrFail($request->user_id);
        $targetGymId = ($gymId === 'all') ? $user->gym_id : $gymId;

        $plan = MembershipPlan::findOrFail($request->plan_id);

        // Deactivate previous active memberships
        UserMembership::where('user_id', $request->user_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($plan->duration_days);

        $planPrice = (float) $plan->price;
        $prevCredit = (float) ($user->credit_balance ?? 0);
        $applyCredit = $request->boolean('use_credit') && $prevCredit > 0;
        $creditApplied = $applyCredit ? min($prevCredit, $planPrice) : 0;
        $remainingToPay = max(0, round($planPrice - $creditApplied, 2));

        $isPaidNow = $request->boolean('paid_now') || $request->filled('payment_method') || ($creditApplied >= $planPrice);
        $paymentStatus = $isPaidNow ? 'paid' : 'pending';

        try {
            $membership = UserMembership::create([
                'user_id' => $request->user_id,
                'gym_id' => $targetGymId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => $paymentStatus,
            ]);

            $receiverId = auth()->check() ? auth()->user()->id : null;
            $source = $request->input('source', $receiverId ? 'admin_panel' : 'mobile_app');

            if ($creditApplied > 0) {
                $newCreditBalance = max(0, round($prevCredit - $creditApplied, 2));
                $user->update(['credit_balance' => $newCreditBalance]);

                UserCreditLog::create([
                    'gym_id' => $targetGymId,
                    'user_id' => $user->id,
                    'membership_id' => $membership->id,
                    'payment_id' => null,
                    'received_by' => $receiverId,
                    'source' => $source,
                    'type' => 'credit_applied_to_plan',
                    'amount' => $creditApplied,
                    'payment_method' => 'credit_balance',
                    'reference_code' => $request->input('reference_number'),
                    'daily_rate' => $plan->duration_days > 0 ? round($plan->price / $plan->duration_days, 2) : 0,
                    'previous_credit' => $prevCredit,
                    'days_added' => 0,
                    'credit_used' => $creditApplied,
                    'resulting_credit' => $newCreditBalance,
                    'notes' => "SALDO A FAVOR APLICADO: Se aplicaron \${$creditApplied} del saldo a favor al contratar/renovar el plan '{$plan->name}'.",
                ]);
            }

            if ($isPaidNow) {
                $payMethod = $request->input('payment_method', 'cash') ?: 'cash';
                $refCode = $request->input('reference_number');

                $paymentNotes = "PAGO INICIAL: Membresía '{$plan->name}' asignada.";
                if ($creditApplied > 0) {
                    $paymentNotes .= " Aplicado \${$creditApplied} de Saldo a Favor.";
                    if ($remainingToPay > 0) {
                        $paymentNotes .= " Saldo restante de \${$remainingToPay} cobrado en " . strtoupper($payMethod) . ".";
                    }
                }

                $currentRate = \App\Services\ExchangeRateService::getCurrentRate($membership->gym_id);
                MembershipPayment::create([
                    'membership_id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'amount' => $plan->price,
                    'amount_ves' => round((float)$plan->price * $currentRate, 2),
                    'exchange_rate' => $currentRate,
                    'payment_currency' => 'USD',
                    'payment_date' => Carbon::now(),
                    'payment_method' => ($creditApplied >= $planPrice && !$request->filled('payment_method')) ? 'other' : $payMethod,
                    'reference_code' => $refCode,
                    'received_by' => auth()->user()->id,
                    'currency' => $plan->currency ?? 'USD',
                    'notes' => $paymentNotes,
                ]);
            }

            $userName = ($user->profile)
                ? $user->profile->first_name . ' ' . $user->profile->last_name
                : $user->email;

            $actionNote = $isPaidNow
                ? "Membresía '{$plan->name}' asignada y PAGADA de contado por el socio {$userName} (Vigencia: {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')})."
                : "Membresía '{$plan->name}' asignada al socio {$userName} (Vigencia: {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}).";

            AdminAuditLog::logAction(
                'CREACION',
                'Asignación de Membresía',
                $actionNote,
                null,
                $membership->toArray(),
                $targetGymId
            );

            $successMsg = $isPaidNow
                ? "¡Membresía '{$plan->name}' asignada y cobro registrado exitosamente!"
                : "Nueva membresía asignada al socio {$userName}. Registra el pago para activarla.";

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'membership' => $membership
                ]);
            }

            return redirect()->back()->with('success', $successMsg);

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
            ->where(function ($q) use ($gymId) {
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
            'discount_value' => (float) $promo->discount_value,
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
     * Approve a pending payment (Binance, Pago Móvil, Transfers) and activate/extend membership or credit abono balance.
     */
    public function approvePendingPayment(Request $request, $id)
    {
        $this->checkAdmin();
        $payment = MembershipPayment::with(['membership.plan', 'membership.user.profile', 'user.profile'])->findOrFail($id);
        $membership = $payment->membership;
        $user = $payment->user ?: ($membership->user ?? null);

        if (!$user) {
            $errMsg = 'Usuario asociado al pago no encontrado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 404);
            }
            return redirect()->back()->withErrors(['error' => $errMsg]);
        }

        $oldMembership = $membership ? $membership->toArray() : null;
        $oldPayment = $payment->toArray();
        $gymId = $membership->gym_id ?? ($user->gym_id ?? 1);

        $notes = $payment->notes ?? '';
        $isTopUp = str_contains($notes, '[TOPUP_PENDIENTE]') || str_contains($notes, '[PAGO_MOVIL_PENDIENTE]') || str_contains(strtolower($notes), 'abono') || !$membership;

        if ($isTopUp) {
            // --- LÓGICA DE ABONO A BILLETERA (SALDO A FAVOR) ---
            $parsedAmount = (float) $payment->amount;
            $parsedAmountVes = (float) ($payment->amount_ves ?? 0);
            $exchangeRate = (float) ($payment->exchange_rate ?? 0);
            $previousCredit = (float) ($user->credit_balance ?? 0);
            $resultingCredit = $previousCredit + $parsedAmount;

            // 1. Registrar Bitácora de Saldo / Abono
            $creditLog = UserCreditLog::create([
                'gym_id' => $gymId,
                'user_id' => $user->id,
                'membership_id' => $membership ? $membership->id : null,
                'payment_id' => $payment->id,
                'received_by' => auth()->id(),
                'source' => 'mobile_app',
                'type' => 'abono_payment',
                'amount' => $parsedAmount,
                'amount_ves' => $parsedAmountVes,
                'exchange_rate' => $exchangeRate > 0 ? $exchangeRate : null,
                'payment_method' => $payment->payment_method,
                'reference_code' => $payment->reference_code,
                'daily_rate' => null,
                'previous_credit' => $previousCredit,
                'days_added' => 0,
                'credit_used' => 0.00,
                'resulting_credit' => $resultingCredit,
                'notes' => "Abono de saldo vía {$payment->payment_method} (Ref: {$payment->reference_code}) verificado y aprobado por Admin #" . auth()->id() . "."
            ]);

            // 2. Si el usuario tiene una membresía activa, verificar si se pueden acreditar días extra automáticamente
            $activeMembership = $membership && $membership->status === 'active' ? $membership : UserMembership::where('user_id', $user->id)->where('status', 'active')->latest('id')->first();
            $daysAdded = 0;

            if ($activeMembership && $activeMembership->plan) {
                $price = (float) $activeMembership->plan->price;
                $durationDays = max(1, (int) ($activeMembership->plan->duration_days ?: 30));
                $dailyRate = $price / $durationDays;

                if ($dailyRate > 0) {
                    $daysAdded = (int) floor($resultingCredit / $dailyRate);
                    if ($daysAdded > 0) {
                        $costToDeduct = $daysAdded * $dailyRate;
                        $previousCreditBeforeExtend = $resultingCredit;
                        $resultingCredit = max(0, round($resultingCredit - $costToDeduct, 2));

                        $today = Carbon::now();
                        $currentEndDate = Carbon::parse($activeMembership->end_date);
                        $baseDate = $currentEndDate->lt($today) ? $today : $currentEndDate;
                        $newEndDate = $baseDate->copy()->addDays($daysAdded)->toDateString();

                        $activeMembership->update([
                            'status' => 'active',
                            'end_date' => $newEndDate,
                            'notes' => trim(($activeMembership->notes ?? '') . " | Auto-extensión por Abono Ref: {$payment->reference_code}"),
                        ]);

                        $creditLog->update([
                            'daily_rate' => $dailyRate,
                            'days_added' => $daysAdded,
                            'credit_used' => $costToDeduct,
                            'resulting_credit' => $resultingCredit,
                        ]);
                    }
                }
            }

            // 3. Actualizar saldo en la tabla de usuarios
            $user->update(['credit_balance' => $resultingCredit]);

            // 4. Actualizar notas del pago
            $cleanNotes = str_replace(['[TOPUP_PENDIENTE]', '[PAGO_MOVIL_PENDIENTE]', '[PENDIENTE_VERIFICACION]', '[PENDIENTE]'], '[APROBADO]', $notes);
            $payment->update([
                'received_by' => auth()->id(),
                'payment_date' => now(),
                'notes' => trim($cleanNotes . " [Aprobado por Admin #" . auth()->id() . " el " . now()->format('d/m/Y H:i') . "]"),
            ]);

            // 5. In-App Notification para el Atleta
            Notification::create([
                'user_id' => $user->id,
                'title' => '¡Abono Aprobado! 🪙',
                'body' => "Tu abono de \${$parsedAmount} (Ref: #{$payment->reference_code}) ha sido verificado y acreditado a tu saldo." . ($daysAdded > 0 ? " ¡Se sumaron +{$daysAdded} días de membresía!" : ""),
                'type' => 'achievement',
                'is_read' => false,
                'createdAt' => now(),
            ]);

            AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $gymId);

            $msg = "Abono de \${$parsedAmount} (Ref: #{$payment->reference_code}) aprobado exitosamente. Saldo disponible: \${$resultingCredit}." . ($daysAdded > 0 ? " (+{$daysAdded} días acreditados)" : "");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'is_abono' => true,
                    'credit_balance' => $resultingCredit,
                    'days_added' => $daysAdded,
                    'payment' => $payment->fresh(['user.profile', 'membership.plan'])
                ]);
            }

            return redirect()->back()->with('success', $msg);

        } else {
            // --- LÓGICA DE PAGO DE MEMBRESÍA COMPLETA ---
            $durationDays = $membership->plan ? ($membership->plan->duration_days ?: 30) : 30;

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
            $cleanNotes = str_replace(['[PENDIENTE_VERIFICACION]', '[PENDIENTE]', '[PAGO_MOVIL_PENDIENTE]'], '[APROBADO]', $notes);
            $payment->update([
                'received_by' => auth()->id(),
                'payment_date' => now(),
                'notes' => trim($cleanNotes . " [Aprobado por Admin #" . auth()->id() . " el " . now()->format('d/m/Y H:i') . "]"),
            ]);

            // In-App Notification para el Atleta
            $planName = $membership->plan->name ?? 'Membresía';
            Notification::create([
                'user_id' => $membership->user_id,
                'title' => '¡Membresía Activada! 🚀',
                'body' => "Tu pago para el plan '{$planName}' (Ref: #{$payment->reference_code}) ha sido verificado con éxito. ¡Vigencia hasta el " . Carbon::parse($endDate)->format('d/m/Y') . "!",
                'type' => 'new_routine',
                'is_read' => false,
                'createdAt' => now(),
            ]);

            // Audit Log
            AdminAuditLog::logAction('UPDATE', 'user_memberships', $membership->id, $oldMembership, $membership->fresh()->toArray(), $gymId);
            AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $gymId);

            $msg = "Pago para el plan '{$planName}' (Ref: #{$payment->reference_code}) aprobado y membresía activada hasta el " . Carbon::parse($endDate)->format('d/m/Y') . ".";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'is_abono' => false,
                    'end_date' => $endDate,
                    'payment' => $payment->fresh(['user.profile', 'membership.plan'])
                ]);
            }

            return redirect()->back()->with('success', $msg);
        }
    }

    /**
     * Reject a pending payment (invalid transaction / reference code).
     */
    public function rejectPendingPayment(Request $request, $id)
    {
        $this->checkAdmin();
        $payment = MembershipPayment::with(['membership.user.profile', 'user.profile'])->findOrFail($id);
        $membership = $payment->membership;
        $user = $payment->user ?: ($membership->user ?? null);

        $reason = $request->input('rejection_reason', 'Referencia de pago no encontrada en la cuenta bancaria o monto no recibido.');

        $oldPayment = $payment->toArray();
        $cleanNotes = str_replace(['[PENDIENTE_VERIFICACION]', '[TOPUP_PENDIENTE]', '[PAGO_MOVIL_PENDIENTE]', '[PENDIENTE]'], '[RECHAZADO]', $payment->notes ?? '');
        $payment->update([
            'notes' => trim($cleanNotes . " [RECHAZADO por Admin #" . auth()->id() . ": {$reason}]"),
        ]);

        if ($membership && in_array($membership->payment_status, ['pending', 'overdue'])) {
            $membership->update([
                'payment_status' => 'overdue',
                'notes' => trim(($membership->notes ?? '') . " | Pago Ref: {$payment->reference_code} RECHAZADO: {$reason}"),
            ]);
        }

        // In-App Notification para el Atleta avisándole del rechazo
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Pago No Aprobado ⚠️',
                'body' => "Tu solicitud de pago (Ref: #{$payment->reference_code}) fue rechazada: {$reason}. Por favor verifica con tu banco o contacta a recepción.",
                'type' => 'payment_reminder',
                'is_read' => false,
                'createdAt' => now(),
            ]);
        }

        AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $payment->gym_id ?? null);

        $msg = "El pago con referencia #{$payment->reference_code} ha sido marcado como RECHAZADO.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'payment' => $payment->fresh()
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * REST API Endpoint for Mobile App / Web to submit payment proof for manual verification.
     * POST /api/v1/payments/submit-proof
     */
    public function apiSubmitPaymentProof(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'gym_id' => 'nullable|exists:gyms,id',
            'amount' => 'required|numeric|min:0.01',
            'amount_ves' => 'nullable|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'reference_code' => 'required|string|max:100',
            'concept_type' => 'nullable|in:abono,membership',
            'plan_id' => 'nullable|exists:membership_plans,id',
            'membership_id' => 'nullable|exists:user_memberships,id',
            'receipt_image' => 'nullable|image|max:10240', // Max 10MB
            'receipt_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);
        $gymId = $request->gym_id ?: ($user->gym_id ?: 1);

        // Process proof image if uploaded
        $receiptUrl = $request->input('receipt_url');
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $uploadDir = public_path('uploads/receipts');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $receiptUrl = asset('uploads/receipts/' . $filename);
        }

        $conceptType = $request->input('concept_type', 'abono');
        $membershipId = $request->membership_id;

        // If it's a membership purchase and no membershipId passed, create/find a pending membership
        if ($conceptType === 'membership' && !$membershipId && $request->filled('plan_id')) {
            $plan = MembershipPlan::findOrFail($request->plan_id);
            $startDate = Carbon::now()->toDateString();
            $endDate = Carbon::now()->addDays($plan->duration_days)->toDateString();

            $membership = UserMembership::create([
                'user_id' => $user->id,
                'gym_id' => $gymId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => 'Suscripción iniciada desde App Móvil (Esperando verificación de comprobante)'
            ]);
            $membershipId = $membership->id;
        }

        $tag = ($conceptType === 'abono') ? '[TOPUP_PENDIENTE]' : '[PENDIENTE_VERIFICACION]';
        $userNotes = $request->filled('notes') ? " - Nota: " . $request->notes : "";
        $fullNotes = "{$tag} Solicitud de pago desde App Móvil ({$request->payment_method}, Ref: {$request->reference_code}){$userNotes}";

        $rate = ($request->filled('exchange_rate') && (float)$request->exchange_rate > 1.0001) 
            ? (float)$request->exchange_rate 
            : \App\Services\ExchangeRateService::getCurrentRate($user->gym_id);
        $amountVes = ($request->filled('amount_ves') && (float)$request->amount_ves > 0)
            ? (float)$request->amount_ves
            : round((float)$request->amount * $rate, 2);

        $payment = MembershipPayment::create([
            'membership_id' => $membershipId,
            'user_id' => $user->id,
            'amount' => $request->amount,
            'amount_ves' => $amountVes,
            'exchange_rate' => $rate,
            'currency' => 'USD',
            'payment_currency' => $request->filled('amount_ves') && (float)$request->amount_ves > 0 ? 'VES' : 'USD',
            'payment_method' => $request->payment_method,
            'payment_date' => Carbon::now(),
            'reference_code' => $request->reference_code,
            'received_by' => null, // NULL indica que requiere verificación manual
            'receipt_url' => $receiptUrl,
            'notes' => $fullNotes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comprobante de pago registrado exitosamente. Será verificado por el equipo del gimnasio a la brevedad.',
            'payment' => [
                'id' => $payment->id,
                'concept_type' => $conceptType,
                'amount' => $payment->amount,
                'amount_ves' => $payment->amount_ves,
                'reference_code' => $payment->reference_code,
                'receipt_url' => $receiptUrl,
                'status' => 'pending_verification'
            ]
        ], 201);
    }

    /**
     * Helper block for role protection (Admin only for global finance metrics and plan creation).
     */
    private function checkAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar las finanzas globales.');
        }
    }

    /**
     * Helper block for cashier and admin payment operations.
     */
    private function checkCashierOrAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin', 'cajero'])) {
            abort(403, 'Acceso Denegado. Solo personal autorizado puede registrar pagos.');
        }
    }
}
