<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPayment;
use App\Models\User;
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

        return view('finanzas.index', compact('plans', 'memberships', 'clients', 'totalCollected', 'pendingAmount'));
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
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder crear un plan de membresía.']);
        }

        MembershipPlan::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'duration_days' => $request->duration_days,
            'price' => $request->price,
            'currency' => $request->currency,
            'includes_trainer' => $request->has('includes_trainer') ? 1 : 0,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Plan de membresía creado exitosamente.');
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
        ]);

        try {
            $membership = UserMembership::findOrFail($request->user_membership_id);

            // Record payment
            MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $membership->user_id,
                'amount' => $request->amount,
                'payment_date' => Carbon::now(),
                'payment_method' => $request->payment_method,
                'reference_code' => $request->reference_number,
                'received_by' => auth()->user()->id,
                'currency' => $membership->plan->currency ?? 'USD',
            ]);

            // Update membership status
            $membership->update([
                'payment_status' => 'paid',
                'status' => 'active',
            ]);

            return redirect()->back()->with('success', 'Pago registrado y membresía activada con éxito.');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al registrar el pago: ' . $errorMessage;
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
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
            UserMembership::create([
                'user_id' => $request->user_id,
                'gym_id' => $targetGymId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => 'pending', // Pending payment registration
            ]);

            return redirect()->back()->with('success', 'Nueva membresía asignada. Registra el pago para activarla.');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al asignar membresía: ' . $errorMessage;
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
        }
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
