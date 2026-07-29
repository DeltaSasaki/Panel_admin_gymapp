<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPayment;
use App\Models\ProductSale;
use App\Models\UserMembership;
use App\Models\AttendanceLog;
use App\Models\AdminAuditLog;
use App\Models\Gym;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashClosingController extends Controller
{
    /**
     * Display Daily Cash Closing & Audit Dashboard.
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $gymId = session('active_gym_id', 'all');
        $targetDate = $request->query('date', Carbon::today()->format('Y-m-d'));

        try {
            $parsedDate = Carbon::parse($targetDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $parsedDate = Carbon::today()->format('Y-m-d');
        }

        // 1. Membership Payments Query
        $mPaymentsQuery = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'receivedBy'])
            ->whereDate('payment_date', $parsedDate);

        if ($gymId !== 'all') {
            $mPaymentsQuery->whereHas('membership', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }
        $membershipPayments = $mPaymentsQuery->orderBy('id', 'desc')->get();

        // 2. Product Sales Query (Tienda / POS)
        $pSalesQuery = ProductSale::with(['user.profile', 'soldBy', 'items.product'])
            ->whereDate('sale_date', $parsedDate);

        if ($gymId !== 'all') {
            $pSalesQuery->where('gym_id', $gymId);
        }
        $productSales = $pSalesQuery->orderBy('id', 'desc')->get();

        // 3. Registered / Renewed Memberships Query
        $membershipsQuery = UserMembership::with(['user.profile', 'plan'])
            ->whereDate('createdAt', $parsedDate);

        if ($gymId !== 'all') {
            $membershipsQuery->where('gym_id', $gymId);
        }
        $newMemberships = $membershipsQuery->orderBy('id', 'desc')->get();

        // 4. Attendance Logs Query
        $attendanceQuery = AttendanceLog::with(['user.profile'])
            ->whereDate('check_in', $parsedDate);

        if ($gymId !== 'all') {
            $attendanceQuery->where('gym_id', $gymId);
        }
        $attendances = $attendanceQuery->orderBy('check_in', 'desc')->get();

        // Totals & Financial Calculations
        $membershipTotal = (float) $membershipPayments->sum('amount');
        $productSalesTotal = (float) $productSales->sum('total_amount');
        $grandTotal = $membershipTotal + $productSalesTotal;

        // Payment Methods Breakdown
        $cashTotal = (float) $membershipPayments->where('payment_method', 'cash')->sum('amount')
            + (float) $productSales->where('payment_method', 'cash')->sum('total_amount');

        $cardTotal = (float) $membershipPayments->where('payment_method', 'card')->sum('amount')
            + (float) $productSales->where('payment_method', 'card')->sum('total_amount');

        $transferTotal = (float) $membershipPayments->where('payment_method', 'transfer')->sum('amount')
            + (float) $productSales->where('payment_method', 'transfer')->sum('total_amount');

        $otherTotal = max(0, $grandTotal - ($cashTotal + $cardTotal + $transferTotal));

        // Audit check if day was closed
        $isClosedQuery = AdminAuditLog::where('action_type', 'EXPORT_DATA')
            ->where('table_name', 'cierre_caja')
            ->where('record_id', $parsedDate);

        if ($gymId !== 'all') {
            $isClosedQuery->where('gym_id', $gymId);
        }
        $closingLog = $isClosedQuery->first();
        $isClosed = !is_null($closingLog);

        $gym = ($gymId !== 'all') ? Gym::find($gymId) : null;

        return view('cierre_caja.index', compact(
            'parsedDate',
            'membershipPayments',
            'productSales',
            'newMemberships',
            'attendances',
            'membershipTotal',
            'productSalesTotal',
            'grandTotal',
            'cashTotal',
            'cardTotal',
            'transferTotal',
            'otherTotal',
            'isClosed',
            'closingLog',
            'gym'
        ));
    }

    /**
     * Register formal Cash Register Close.
     */
    public function closeDay(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $gymId = session('active_gym_id', 'all');
        $date = $request->date;

        $targetGymId = ($gymId === 'all') ? null : $gymId;

        AdminAuditLog::logAction(
            'EXPORT_DATA',
            'cierre_caja',
            $date,
            null,
            [
                'date' => $date,
                'closed_by' => auth()->user()->name,
                'notes' => $request->notes,
                'closed_at' => Carbon::now()->toDateTimeString()
            ],
            $targetGymId
        );

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "¡Cierre de caja del día {$date} registrado con éxito!"
            ]);
        }

        return redirect()->back()->with('success', "¡Cierre de caja del día {$date} registrado con éxito!");
    }

    /**
     * Helper block for role protection.
     */
    private function checkAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden realizar el cierre de caja.');
        }
    }
}
