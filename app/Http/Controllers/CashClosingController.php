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
        $data = $this->getCashClosingData($request);
        return view('cierre_caja.index', $data);
    }

    /**
     * Export professional Cash Closing PDF using mPDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getCashClosingData($request);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5
        ]);

        $html = view('cierre_caja.pdf', $data)->render();
        $mpdf->WriteHTML($html);

        $slugPeriod = \Illuminate\Support\Str::slug($data['periodLabel']);
        $filename = 'Cierre_Caja_' . $slugPeriod . '_' . date('Ymd_His') . '.pdf';

        return response($mpdf->Output($filename, 'I'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Fetch & process all financial metrics and queries for cash closing.
     */
    private function getCashClosingData(Request $request)
    {
        $this->checkAdmin();

        $gymId = $this->getActiveGymId();
        $period = $request->query('period', 'today');
        $targetDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        $periodLabel = 'Hoy';
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        if ($period === 'yesterday') {
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
            $periodLabel = 'Ayer (' . $startDate->format('d/m/Y') . ')';
        } elseif ($period === 'this_week') {
            $startDate = Carbon::now()->startOfWeek()->startOfDay();
            $endDate = Carbon::now()->endOfWeek()->endOfDay();
            $periodLabel = 'Esta Semana (' . $startDate->format('d/m') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($period === 'last_week') {
            $startDate = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
            $endDate = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
            $periodLabel = 'Semana Anterior (' . $startDate->format('d/m') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->endOfMonth()->endOfDay();
            $periodLabel = 'Mes Actual (' . $startDate->format('m/Y') . ')';
        } elseif ($period === 'custom' && $startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
            $periodLabel = 'Rango (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($period === 'specific' || $request->has('date')) {
            try {
                $startDate = Carbon::parse($targetDate)->startOfDay();
                $endDate = Carbon::parse($targetDate)->endOfDay();
                $periodLabel = Carbon::parse($targetDate)->format('d/m/Y');
            } catch (\Exception $e) {
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Hoy (' . $startDate->format('d/m/Y') . ')';
            }
        }

        $parsedDate = $startDate->format('Y-m-d');

        // 1. Membership Payments Query
        $mPaymentsQuery = MembershipPayment::with(['membership.user.profile', 'membership.plan', 'receivedBy'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($gymId !== 'all') {
            $mPaymentsQuery->whereHas('membership', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }
        $membershipPayments = $mPaymentsQuery->orderBy('id', 'desc')->get();

        // 2. Product Sales Query (Tienda / POS)
        $pSalesQuery = ProductSale::with(['user.profile', 'soldBy', 'items.product'])
            ->whereBetween('sale_date', [$startDate, $endDate]);

        if ($gymId !== 'all') {
            $pSalesQuery->where('gym_id', $gymId);
        }
        $productSales = $pSalesQuery->orderBy('id', 'desc')->get();

        // 3. Registered / Renewed Memberships Query
        $membershipsQuery = UserMembership::with(['user.profile', 'plan'])
            ->whereBetween('createdAt', [$startDate, $endDate]);

        if ($gymId !== 'all') {
            $membershipsQuery->where('gym_id', $gymId);
        }
        $newMemberships = $membershipsQuery->orderBy('id', 'desc')->get();

        // 4. Attendance Logs Query
        $attendanceQuery = AttendanceLog::with(['user.profile'])
            ->whereBetween('check_in', [$startDate, $endDate]);

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

        // Exchange Rate (VES Factor) calculations
        $dollarRate = \App\Services\ExchangeRateService::getCurrentRate($gymId);

        // Audit check if day was closed
        $isClosedQuery = AdminAuditLog::where('action_type', 'EXPORT_DATA')
            ->where('table_name', 'cierre_caja')
            ->where('record_id', $parsedDate);

        if ($gymId !== 'all') {
            $isClosedQuery->where('gym_id', $gymId);
        }
        $closingLog = $isClosedQuery->first();
        $isClosed = !is_null($closingLog);

        // If day was closed, lock the rate from the audit snapshot
        if ($isClosed && !empty($closingLog->new_values) && is_array($closingLog->new_values)) {
            if (!empty($closingLog->new_values['dollar_rate']) && (float)$closingLog->new_values['dollar_rate'] > 1.0001) {
                $dollarRate = (float)$closingLog->new_values['dollar_rate'];
            }
        }

        // Calculate VES totals by summing the FROZEN transaction amounts (Historical Immutability)
        $membershipTotalVes = (float) $membershipPayments->sum(function($p) use ($dollarRate) {
            return ($p->amount_ves && (float)$p->amount_ves > ((float)$p->amount * 1.0001))
                ? (float)$p->amount_ves
                : ((float)$p->amount * (($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate));
        });

        $productSalesTotalVes = (float) $productSales->sum(function($s) use ($dollarRate) {
            return ($s->total_amount_ves && (float)$s->total_amount_ves > ((float)$s->total_amount * 1.0001))
                ? (float)$s->total_amount_ves
                : ((float)$s->total_amount * (($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate));
        });

        $grandTotalVes = round($membershipTotalVes + $productSalesTotalVes, 2);

        // Payment method breakdown in VES based on frozen transactions
        $cashTotalVes = (float) $membershipPayments->where('payment_method', 'cash')->sum(function($p) use ($dollarRate) {
            return ($p->amount_ves && (float)$p->amount_ves > ((float)$p->amount * 1.0001))
                ? (float)$p->amount_ves
                : ((float)$p->amount * (($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate));
        }) + (float) $productSales->where('payment_method', 'cash')->sum(function($s) use ($dollarRate) {
            return ($s->total_amount_ves && (float)$s->total_amount_ves > ((float)$s->total_amount * 1.0001))
                ? (float)$s->total_amount_ves
                : ((float)$s->total_amount * (($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate));
        });

        $cardTotalVes = (float) $membershipPayments->where('payment_method', 'card')->sum(function($p) use ($dollarRate) {
            return ($p->amount_ves && (float)$p->amount_ves > ((float)$p->amount * 1.0001))
                ? (float)$p->amount_ves
                : ((float)$p->amount * (($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate));
        }) + (float) $productSales->where('payment_method', 'card')->sum(function($s) use ($dollarRate) {
            return ($s->total_amount_ves && (float)$s->total_amount_ves > ((float)$s->total_amount * 1.0001))
                ? (float)$s->total_amount_ves
                : ((float)$s->total_amount * (($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate));
        });

        $transferTotalVes = (float) $membershipPayments->where('payment_method', 'transfer')->sum(function($p) use ($dollarRate) {
            return ($p->amount_ves && (float)$p->amount_ves > ((float)$p->amount * 1.0001))
                ? (float)$p->amount_ves
                : ((float)$p->amount * (($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate));
        }) + (float) $productSales->where('payment_method', 'transfer')->sum(function($s) use ($dollarRate) {
            return ($s->total_amount_ves && (float)$s->total_amount_ves > ((float)$s->total_amount * 1.0001))
                ? (float)$s->total_amount_ves
                : ((float)$s->total_amount * (($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate));
        });

        $otherTotalVes = max(0, $grandTotalVes - ($cashTotalVes + $cardTotalVes + $transferTotalVes));

        $gym = ($gymId !== 'all') ? Gym::find($gymId) : null;

        return compact(
            'membershipPayments',
            'productSales',
            'newMemberships',
            'attendances',
            'membershipTotal',
            'membershipTotalVes',
            'productSalesTotal',
            'productSalesTotalVes',
            'grandTotal',
            'grandTotalVes',
            'cashTotal',
            'cashTotalVes',
            'cardTotal',
            'cardTotalVes',
            'transferTotal',
            'transferTotalVes',
            'otherTotal',
            'otherTotalVes',
            'dollarRate',
            'parsedDate',
            'period',
            'periodLabel',
            'isClosed',
            'closingLog',
            'gym'
        );
    }

    /**
     * Register formal Cash Register Close with immutable snapshot.
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
        $data = $this->getCashClosingData($request);

        AdminAuditLog::logAction(
            'EXPORT_DATA',
            'cierre_caja',
            $date,
            null,
            [
                'date' => $date,
                'closed_by' => auth()->user()->name,
                'notes' => $request->notes,
                'dollar_rate' => $data['dollarRate'],
                'grand_total_usd' => $data['grandTotal'],
                'grand_total_ves' => $data['grandTotalVes'],
                'membership_total_usd' => $data['membershipTotal'],
                'membership_total_ves' => $data['membershipTotalVes'],
                'product_sales_total_usd' => $data['productSalesTotal'],
                'product_sales_total_ves' => $data['productSalesTotalVes'],
                'cash_total_usd' => $data['cashTotal'],
                'cash_total_ves' => $data['cashTotalVes'],
                'card_total_usd' => $data['cardTotal'],
                'card_total_ves' => $data['cardTotalVes'],
                'transfer_total_usd' => $data['transferTotal'],
                'transfer_total_ves' => $data['transferTotalVes'],
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
        if (!in_array(auth()->user()->role, ['admin', 'superadmin', 'cajero'])) {
            abort(403, 'Acceso Denegado. Solo administradores y cajeros pueden realizar el cierre de caja.');
        }
    }
}
