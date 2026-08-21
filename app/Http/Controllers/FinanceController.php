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

        // Fetch pending verification payments (Binance, Pago Móvil, Transfers from Mobile App / API)
        // Only include payments for memberships that are still pending or overdue (requiring manual admin verification)
        $pendingVerificationPayments = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'user.profile'])
            ->whereHas('membership', function ($q) use ($gymId) {
                $q->where('status', 'active')->whereIn('payment_status', ['pending', 'overdue']);
                if ($gymId !== 'all') {
                    $q->where('gym_id', $gymId);
                }
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
            $durationDays = max(1, (int) $plan->duration_days);
            $dailyRate = (float) $plan->price / $durationDays;

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

            $receiverId = auth()->check() ? auth()->user()->id : null;
            $source = $request->input('source', $receiverId ? 'admin_panel' : 'mobile_app');

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
                    'daily_rate' => $dailyRate,
                    'previous_credit' => $prevCredit,
                    'days_added' => 0,
                    'credit_used' => 0.00,
                    'resulting_credit' => $newCredit,
                    'notes' => $notes,
                ]);
                $creditLog->load(['user.profile', 'membership.plan', 'receiver.profile', 'payment']);

                if ($membership->payment_status === 'pending') {
                    $membership->update(['payment_status' => 'paid']);
                }

                $msg = "Abono de \${$payAmount} guardado como Saldo a Favor. Crédito acumulado total: \${$newCredit}. Faltan \$" . number_format($dailyRate - $newCredit, 2) . " para 1 día extra.";

                if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'extra_days' => 0,
                        'credit_balance' => $newCredit,
                        'log' => $creditLog,
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
            $creditText = $newCredit > 0 ? " (Saldo a favor restante: \${$newCredit})" : "";
            $notes = "ABONO ADELANTADO: Pago de \${$payAmount}" . ($prevCredit > 0 ? " + \${$prevCredit} saldo previo" : "") . " otorgó +{$extraDays} día(s) extra a \${$formattedRate}/día. Nueva vigencia hasta " . $newEndDate->format('d/m/Y') . "{$creditText}.";

            $payment = MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'amount' => $payAmount,
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
                'daily_rate' => $dailyRate,
                'previous_credit' => $prevCredit,
                'days_added' => $extraDays,
                'credit_used' => $costUsed,
                'resulting_credit' => $newCredit,
                'notes' => $notes,
            ]);
            $creditLog->load(['user.profile', 'membership.plan', 'receiver.profile', 'payment']);

            AdminAuditLog::logAction(
                'TRANSACCION',
                'Abono Adelantado',
                "Abono de \${$payAmount} registrado para {$user->email} (+{$extraDays} días extra hasta " . $newEndDate->format('d/m/Y') . ").",
                $oldData,
                $membership->toArray(),
                $membership->gym_id
            );

            $msg = "¡Abono de \${$payAmount} registrado exitosamente! Se otorgaron +{$extraDays} día(s) adicionales de vigencia." . ($newCredit > 0 ? " Saldo a favor: \${$newCredit}" : "");

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'extra_days' => $extraDays,
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                    'new_end_date_formatted' => $newEndDate->format('d/m/Y'),
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
        $this->checkAdmin();
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

                MembershipPayment::create([
                    'membership_id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'amount' => $plan->price,
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
     * Approve a pending payment (Binance, Pago Móvil, Transfers) and activate/extend membership.
     * SOPORTA ABONOS A LA BILLETERA ([TOPUP_PENDIENTE]).
     */
    public function approvePendingPayment(Request $request, $id)
    {
        $this->checkAdmin();
        $payment = \App\Models\MembershipPayment::with(['membership.plan', 'membership.user.profile'])->findOrFail($id);
        $membership = $payment->membership;

        if (!$membership) {
            return redirect()->back()->withErrors(['error' => 'Membresía no encontrada para este pago.']);
        }

        $oldMembership = $membership->toArray();
        $oldPayment = $payment->toArray();

        $isTopUp = str_contains($payment->notes, '[TOPUP_PENDIENTE]');

        if ($isTopUp) {
            // --- LÓGICA DE ABONO A BILLETERA ---
            $user = $membership->user;
            if (!$user) {
                return redirect()->back()->withErrors(['error' => 'Usuario no encontrado para esta membresía.']);
            }

            $parsedAmount = (float) $payment->amount;
            $previousCredit = (float) $user->credit_balance;
            $resultingCredit = $previousCredit + $parsedAmount;

            // 1. Agregar a la billetera
            \App\Models\UserCreditLog::create([
                'gym_id' => $membership->gym_id,
                'user_id' => $user->id,
                'source' => 'transfer',
                'type' => 'add',
                'amount' => $parsedAmount,
                'payment_method' => $payment->payment_method,
                'reference_code' => $payment->reference_code,
                'previous_credit' => $previousCredit,
                'resulting_credit' => $resultingCredit,
                'notes' => "Abono de saldo vía {$payment->payment_method} (Aprobado por admin)."
            ]);

            $price = $membership->plan ? (float) $membership->plan->price : 0;
            $durationDays = $membership->plan ? ($membership->plan->duration_days ?: 30) : 30;
            $daysAdded = 0;

            if ($price > 0 && $durationDays > 0) {
                $dailyRate = $price / $durationDays;
                $daysAdded = floor($resultingCredit / $dailyRate);

                if ($daysAdded > 0) {
                    $costToDeduct = $daysAdded * $dailyRate;
                    $previousCreditBeforeExtend = $resultingCredit;
                    $resultingCredit -= $costToDeduct;

                    $today = \Carbon\Carbon::now();
                    $currentEndDate = \Carbon\Carbon::parse($membership->end_date);

                    $baseDate = $currentEndDate->lt($today) ? $today : $currentEndDate;
                    $newEndDate = $baseDate->copy()->addDays($daysAdded)->toDateString();

                    $membership->update([
                        'status' => 'active',
                        'end_date' => $newEndDate,
                        'notes' => trim(($membership->notes ?? '') . " | Auto-extensión por Abono Ref: {$payment->reference_code}"),
                    ]);

                    \App\Models\UserCreditLog::create([
                        'gym_id' => $membership->gym_id,
                        'user_id' => $user->id,
                        'membership_id' => $membership->id,
                        'source' => 'system',
                        'type' => 'use',
                        'amount' => $costToDeduct,
                        'daily_rate' => $dailyRate,
                        'previous_credit' => $previousCreditBeforeExtend,
                        'days_added' => $daysAdded,
                        'credit_used' => $costToDeduct,
                        'resulting_credit' => $resultingCredit,
                        'notes' => "Extensión automática: +{$daysAdded} días por abono."
                    ]);
                }
            }

            // Actualizar saldo final del usuario
            $user->update(['credit_balance' => $resultingCredit]);

            // Actualizar notas del pago
            $payment->update([
                'received_by' => auth()->id(),
                'payment_date' => now(),
                'notes' => str_replace('[TOPUP_PENDIENTE]', '[TOPUP_APROBADO]', $payment->notes) . " [Aprobado por Admin #" . auth()->id() . "]",
            ]);

            \App\Models\AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $membership->gym_id);

            if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                $userName = trim(($membership->user->profile->first_name ?? '') . ' ' . ($membership->user->profile->last_name ?? ''));
                activity()
                    ->performedOn($membership)
                    ->causedBy(auth()->user())
                    ->log("Aprobación de ABONO manual {$payment->payment_method} (Ref: {$payment->reference_code}) para socio {$userName}.");
            }

            return redirect()->back()->with('success', 'Abono manual aprobado y saldo acreditado correctamente.');

        } else {
            // --- LÓGICA NORMAL DE PAGO DE MEMBRESÍA ---
            $durationDays = $membership->plan ? ($membership->plan->duration_days ?: 30) : 30;

            // Calculate new start and end dates from today
            $startDate = \Carbon\Carbon::now()->toDateString();
            $endDate = \Carbon\Carbon::now()->addDays($durationDays)->toDateString();

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
            \App\Models\AdminAuditLog::logAction('UPDATE', 'user_memberships', $membership->id, $oldMembership, $membership->fresh()->toArray(), $membership->gym_id);
            \App\Models\AdminAuditLog::logAction('UPDATE', 'membership_payments', $payment->id, $oldPayment, $payment->fresh()->toArray(), $membership->gym_id);

            if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                $userName = trim(($membership->user->profile->first_name ?? '') . ' ' . ($membership->user->profile->last_name ?? ''));
                activity()
                    ->performedOn($membership)
                    ->causedBy(auth()->user())
                    ->log("Aprobación manual de pago {$payment->payment_method} (Ref: {$payment->reference_code}) para socio {$userName}. Membresía activada hasta {$endDate}");
            }

            return redirect()->back()->with('success', 'Pago aprobado y membresía activada/extendida con éxito.');
        }
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
