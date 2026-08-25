<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPayment;
use App\Models\ProductSale;
use App\Models\UserMembership;
use App\Models\AttendanceLog;
use App\Models\AdminAuditLog;
use App\Models\CashClosing;
use App\Models\Cashier;
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
        $registerType = $request->query('register_type', 'all');
        if (!in_array($registerType, ['all', 'memberships', 'pos'])) {
            $registerType = 'all';
        }

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

        // Current exchange rate for fallback on unclosed or open transactions
        $liveRate = \App\Services\ExchangeRateService::getCurrentRate($gymId);
        $dollarRate = $liveRate;

        // Check formal CashClosing records
        $effectiveGymFilter = ($gymId !== 'all') ? $gymId : null;

        $closingMemberships = CashClosing::with(['cashier.user.profile', 'closedBy.profile'])
            ->where('closing_date', $parsedDate)
            ->where('register_type', 'memberships')
            ->when($effectiveGymFilter, fn($q) => $q->where('gym_id', $effectiveGymFilter))
            ->latest('id')
            ->first();

        $closingPos = CashClosing::with(['cashier.user.profile', 'closedBy.profile'])
            ->where('closing_date', $parsedDate)
            ->where('register_type', 'pos')
            ->when($effectiveGymFilter, fn($q) => $q->where('gym_id', $effectiveGymFilter))
            ->latest('id')
            ->first();

        $closingGlobal = CashClosing::with(['cashier.user.profile', 'closedBy.profile'])
            ->where('closing_date', $parsedDate)
            ->where('register_type', 'all')
            ->when($effectiveGymFilter, fn($q) => $q->where('gym_id', $effectiveGymFilter))
            ->latest('id')
            ->first();

        $isMembershipsClosed = !is_null($closingMemberships) && $closingMemberships->status === 'closed';
        $isPosClosed = !is_null($closingPos) && $closingPos->status === 'closed';
        $isGlobalClosed = !is_null($closingGlobal) && $closingGlobal->status === 'closed';

        // Set active register status & title
        if ($registerType === 'memberships') {
            $isClosed = $isMembershipsClosed;
            $savedClosing = $closingMemberships;
            $registerTitle = 'Caja 1: Recepción y Membresías';
        } elseif ($registerType === 'pos') {
            $isClosed = $isPosClosed;
            $savedClosing = $closingPos;
            $registerTitle = 'Caja 2: Tienda y POS Mostrador';
        } else {
            $isClosed = $isGlobalClosed || ($isMembershipsClosed && $isPosClosed);
            $savedClosing = $closingGlobal ?? ($isMembershipsClosed ? $closingMemberships : $closingPos);
            $registerTitle = 'Consolidado General (Todas las Cajas)';
        }

        // Lock dollar rate if closed
        if ($isClosed && $savedClosing && (float)$savedClosing->exchange_rate > 1.0001) {
            $dollarRate = (float)$savedClosing->exchange_rate;
        }

        // Calculate dynamic sums from each individual transaction's frozen fields
        $mTotal = (float) $membershipPayments->sum('amount');
        $mTotalVes = (float) $membershipPayments->sum(function($p) use ($dollarRate) {
            if ($p->amount_ves && (float)$p->amount_ves > 0) {
                return (float)$p->amount_ves;
            }
            $rate = ($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate;
            return (float)$p->amount * $rate;
        });

        $mCash = (float) $membershipPayments->where('payment_method', 'cash')->sum('amount');
        $mCashVes = (float) $membershipPayments->where('payment_method', 'cash')->sum(function($p) use ($dollarRate) {
            if ($p->amount_ves && (float)$p->amount_ves > 0) return (float)$p->amount_ves;
            $rate = ($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate;
            return (float)$p->amount * $rate;
        });

        $mCard = (float) $membershipPayments->where('payment_method', 'card')->sum('amount');
        $mCardVes = (float) $membershipPayments->where('payment_method', 'card')->sum(function($p) use ($dollarRate) {
            if ($p->amount_ves && (float)$p->amount_ves > 0) return (float)$p->amount_ves;
            $rate = ($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate;
            return (float)$p->amount * $rate;
        });

        $mTransfer = (float) $membershipPayments->where('payment_method', 'transfer')->sum('amount');
        $mTransferVes = (float) $membershipPayments->where('payment_method', 'transfer')->sum(function($p) use ($dollarRate) {
            if ($p->amount_ves && (float)$p->amount_ves > 0) return (float)$p->amount_ves;
            $rate = ($p->exchange_rate && (float)$p->exchange_rate > 1.0001) ? (float)$p->exchange_rate : $dollarRate;
            return (float)$p->amount * $rate;
        });
        $mOther = max(0, $mTotal - ($mCash + $mCard + $mTransfer));
        $mOtherVes = max(0, $mTotalVes - ($mCashVes + $mCardVes + $mTransferVes));

        $pTotal = (float) $productSales->sum('total_amount');
        $pTotalVes = (float) $productSales->sum(function($s) use ($dollarRate) {
            if ($s->total_amount_ves && (float)$s->total_amount_ves > 0) {
                return (float)$s->total_amount_ves;
            }
            $rate = ($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate;
            return (float)$s->total_amount * $rate;
        });

        $pCash = (float) $productSales->where('payment_method', 'cash')->sum('total_amount');
        $pCashVes = (float) $productSales->where('payment_method', 'cash')->sum(function($s) use ($dollarRate) {
            if ($s->total_amount_ves && (float)$s->total_amount_ves > 0) return (float)$s->total_amount_ves;
            $rate = ($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate;
            return (float)$s->total_amount * $rate;
        });

        $pCard = (float) $productSales->where('payment_method', 'card')->sum('total_amount');
        $pCardVes = (float) $productSales->where('payment_method', 'card')->sum(function($s) use ($dollarRate) {
            if ($s->total_amount_ves && (float)$s->total_amount_ves > 0) return (float)$s->total_amount_ves;
            $rate = ($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate;
            return (float)$s->total_amount * $rate;
        });

        $pTransfer = (float) $productSales->where('payment_method', 'transfer')->sum('total_amount');
        $pTransferVes = (float) $productSales->where('payment_method', 'transfer')->sum(function($s) use ($dollarRate) {
            if ($s->total_amount_ves && (float)$s->total_amount_ves > 0) return (float)$s->total_amount_ves;
            $rate = ($s->exchange_rate && (float)$s->exchange_rate > 1.0001) ? (float)$s->exchange_rate : $dollarRate;
            return (float)$s->total_amount * $rate;
        });
        $pOther = max(0, $pTotal - ($pCash + $pCard + $pTransfer));
        $pOtherVes = max(0, $pTotalVes - ($pCashVes + $pCardVes + $pTransferVes));

        // If register is closed, use the frozen values from CashClosing
        if ($registerType === 'memberships') {
            if ($isClosed && $closingMemberships) {
                $grandTotal = (float)$closingMemberships->total_usd;
                $grandTotalVes = (float)$closingMemberships->total_ves;
                $cashTotal = (float)$closingMemberships->cash_usd;
                $cashTotalVes = (float)$closingMemberships->cash_ves;
                $cardTotal = (float)$closingMemberships->card_usd;
                $cardTotalVes = (float)$closingMemberships->card_ves;
                $transferTotal = (float)$closingMemberships->transfer_usd;
                $transferTotalVes = (float)$closingMemberships->transfer_ves;
                $otherTotal = (float)$closingMemberships->other_usd;
                $otherTotalVes = (float)$closingMemberships->other_ves;
            } else {
                $grandTotal = $mTotal;
                $grandTotalVes = $mTotalVes;
                $cashTotal = $mCash;
                $cashTotalVes = $mCashVes;
                $cardTotal = $mCard;
                $cardTotalVes = $mCardVes;
                $transferTotal = $mTransfer;
                $transferTotalVes = $mTransferVes;
                $otherTotal = $mOther;
                $otherTotalVes = $mOtherVes;
            }
            $membershipTotal = $grandTotal;
            $membershipTotalVes = $grandTotalVes;
            $productSalesTotal = 0;
            $productSalesTotalVes = 0;
        } elseif ($registerType === 'pos') {
            if ($isClosed && $closingPos) {
                $grandTotal = (float)$closingPos->total_usd;
                $grandTotalVes = (float)$closingPos->total_ves;
                $cashTotal = (float)$closingPos->cash_usd;
                $cashTotalVes = (float)$closingPos->cash_ves;
                $cardTotal = (float)$closingPos->card_usd;
                $cardTotalVes = (float)$closingPos->card_ves;
                $transferTotal = (float)$closingPos->transfer_usd;
                $transferTotalVes = (float)$closingPos->transfer_ves;
                $otherTotal = (float)$closingPos->other_usd;
                $otherTotalVes = (float)$closingPos->other_ves;
            } else {
                $grandTotal = $pTotal;
                $grandTotalVes = $pTotalVes;
                $cashTotal = $pCash;
                $cashTotalVes = $pCashVes;
                $cardTotal = $pCard;
                $cardTotalVes = $pCardVes;
                $transferTotal = $pTransfer;
                $transferTotalVes = $pTransferVes;
                $otherTotal = $pOther;
                $otherTotalVes = $pOtherVes;
            }
            $membershipTotal = 0;
            $membershipTotalVes = 0;
            $productSalesTotal = $grandTotal;
            $productSalesTotalVes = $grandTotalVes;
        } else {
            // General / Consolidado
            if ($isClosed && $closingGlobal) {
                $grandTotal = (float)$closingGlobal->total_usd;
                $grandTotalVes = (float)$closingGlobal->total_ves;
                $cashTotal = (float)$closingGlobal->cash_usd;
                $cashTotalVes = (float)$closingGlobal->cash_ves;
                $cardTotal = (float)$closingGlobal->card_usd;
                $cardTotalVes = (float)$closingGlobal->card_ves;
                $transferTotal = (float)$closingGlobal->transfer_usd;
                $transferTotalVes = (float)$closingGlobal->transfer_ves;
                $otherTotal = (float)$closingGlobal->other_usd;
                $otherTotalVes = (float)$closingGlobal->other_ves;
                $membershipTotal = $isMembershipsClosed ? (float)$closingMemberships->total_usd : $mTotal;
                $membershipTotalVes = $isMembershipsClosed ? (float)$closingMemberships->total_ves : $mTotalVes;
                $productSalesTotal = $isPosClosed ? (float)$closingPos->total_usd : $pTotal;
                $productSalesTotalVes = $isPosClosed ? (float)$closingPos->total_ves : $pTotalVes;
            } elseif ($isMembershipsClosed && $isPosClosed && $closingMemberships && $closingPos) {
                $membershipTotal = (float)$closingMemberships->total_usd;
                $membershipTotalVes = (float)$closingMemberships->total_ves;
                $productSalesTotal = (float)$closingPos->total_usd;
                $productSalesTotalVes = (float)$closingPos->total_ves;
                $grandTotal = $membershipTotal + $productSalesTotal;
                $grandTotalVes = $membershipTotalVes + $productSalesTotalVes;
                $cashTotal = (float)$closingMemberships->cash_usd + (float)$closingPos->cash_usd;
                $cashTotalVes = (float)$closingMemberships->cash_ves + (float)$closingPos->cash_ves;
                $cardTotal = (float)$closingMemberships->card_usd + (float)$closingPos->card_usd;
                $cardTotalVes = (float)$closingMemberships->card_ves + (float)$closingPos->card_ves;
                $transferTotal = (float)$closingMemberships->transfer_usd + (float)$closingPos->transfer_usd;
                $transferTotalVes = (float)$closingMemberships->transfer_ves + (float)$closingPos->transfer_ves;
                $otherTotal = (float)$closingMemberships->other_usd + (float)$closingPos->other_usd;
                $otherTotalVes = (float)$closingMemberships->other_ves + (float)$closingPos->other_ves;
            } else {
                $membershipTotal = $mTotal;
                $membershipTotalVes = $mTotalVes;
                $productSalesTotal = $pTotal;
                $productSalesTotalVes = $pTotalVes;
                $grandTotal = $mTotal + $pTotal;
                $grandTotalVes = round($mTotalVes + $pTotalVes, 2);
                $cashTotal = $mCash + $pCash;
                $cashTotalVes = $mCashVes + $pCashVes;
                $cardTotal = $mCard + $pCard;
                $cardTotalVes = $mCardVes + $pCardVes;
                $transferTotal = $mTransfer + $pTransfer;
                $transferTotalVes = $mTransferVes + $pTransferVes;
                $otherTotal = $mOther + $pOther;
                $otherTotalVes = $mOtherVes + $pOtherVes;
            }
        }

        $gym = ($gymId !== 'all') ? Gym::find($gymId) : null;
        $closingRecord = $savedClosing;

        return compact(
            'membershipPayments',
            'productSales',
            'newMemberships',
            'attendances',
            'membershipTotal',
            'membershipTotalVes',
            'productSalesTotal',
            'productSalesTotalVes',
            'mTotal',
            'mTotalVes',
            'mCash',
            'mCashVes',
            'mCard',
            'mCardVes',
            'mTransfer',
            'mTransferVes',
            'pTotal',
            'pTotalVes',
            'pCash',
            'pCashVes',
            'pCard',
            'pCardVes',
            'pTransfer',
            'pTransferVes',
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
            'liveRate',
            'parsedDate',
            'period',
            'periodLabel',
            'registerType',
            'registerTitle',
            'isClosed',
            'closingRecord',
            'closingMemberships',
            'closingPos',
            'closingGlobal',
            'isMembershipsClosed',
            'isPosClosed',
            'isGlobalClosed',
            'gym'
        );
    }

    /**
     * Register formal Cash Register Close with immutable snapshot in cash_closings table.
     */
    public function closeDay(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'date' => 'required|date',
            'register_type' => 'required|string|in:all,memberships,pos',
            'physical_cash_usd' => 'nullable|numeric|min:0',
            'physical_cash_ves' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $gymId = session('active_gym_id', 'all');
        $date = $request->date;
        $registerType = $request->register_type;

        $targetGymId = ($gymId === 'all') ? (Gym::first()->id ?? 1) : (int)$gymId;
        $data = $this->getCashClosingData($request);

        $cashier = Cashier::where('user_id', auth()->id())
            ->where('gym_id', $targetGymId)
            ->first();

        $physicalUsd = (float)($request->physical_cash_usd ?? $data['cashTotal']);
        $physicalVes = (float)($request->physical_cash_ves ?? $data['cashTotalVes']);

        $closing = CashClosing::updateOrCreate(
            [
                'gym_id' => $targetGymId,
                'closing_date' => $date,
                'register_type' => $registerType,
            ],
            [
                'cashier_id' => $cashier ? $cashier->id : null,
                'closed_by' => auth()->id(),
                'exchange_rate' => $data['dollarRate'],
                'total_usd' => $data['grandTotal'],
                'total_ves' => $data['grandTotalVes'],
                'cash_usd' => $data['cashTotal'],
                'card_usd' => $data['cardTotal'],
                'transfer_usd' => $data['transferTotal'],
                'other_usd' => $data['otherTotal'],
                'cash_ves' => $data['cashTotalVes'],
                'card_ves' => $data['cardTotalVes'],
                'transfer_ves' => $data['transferTotalVes'],
                'other_ves' => $data['otherTotalVes'],
                'expected_cash_usd' => $data['cashTotal'],
                'actual_cash_usd' => $physicalUsd,
                'difference_usd' => round($physicalUsd - (float)$data['cashTotal'], 2),
                'expected_cash_ves' => $data['cashTotalVes'],
                'actual_cash_ves' => $physicalVes,
                'difference_ves' => round($physicalVes - (float)$data['cashTotalVes'], 2),
                'memberships_count' => $data['membershipPayments']->count(),
                'sales_count' => $data['productSales']->count(),
                'status' => 'closed',
                'notes' => $request->notes,
                'closed_at' => Carbon::now(),
            ]
        );

        // Also record audit log for security history
        AdminAuditLog::logAction(
            'EXPORT_DATA',
            'cash_closings',
            $closing->id,
            null,
            $closing->toArray(),
            $targetGymId
        );

        $regName = match($registerType) {
            'memberships' => 'Caja 1: Recepción y Membresías',
            'pos' => 'Caja 2: Tienda POS',
            default => 'Caja General (Consolidada)'
        };

        $msg = "¡Cierre formal de {$regName} del día {$date} registrado con éxito con tasa inmutable de Bs. " . number_format($data['dollarRate'], 2, ',', '.') . "!";

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'closing' => $closing
            ]);
        }

        return redirect()->back()->with('success', $msg);
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

