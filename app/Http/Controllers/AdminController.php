<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Trainer;
use App\Models\Gym;
use App\Models\WorkoutRoutine;
use App\Models\MealPlan;
use App\Models\WorkoutSession;
use App\Models\BodyMeasurement;
use App\Models\UserMealPlan;
use App\Models\UserAssignedRoutine;
use App\Models\Exercise;
use App\Models\RoutineDay;
use App\Models\RoutineExercise;
use App\Models\MembershipPayment;
use App\Models\MealPlanDay;
use App\Models\Recipe;
use App\Models\Notification;
use App\Models\AttendanceLog;
use App\Models\UserMembership;
use App\Models\ProductSale;
use App\Models\InventoryProduct;
use App\Models\UserTrainerAssignment;

class AdminController extends Controller
{
    /**
     * Dashboard view.
     */
    public function dashboard()
    {
        $gymId = $this->getActiveGymId();

        $totalClients = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->count();

        $activeClientsToday = WorkoutSession::whereHas('user', function ($q) use ($gymId) {
            $q->when($gymId !== 'all', function ($sq) use ($gymId) {
                $sq->where('gym_id', $gymId);
            });
        })->whereDate('session_date', Carbon::today())->count();

        $totalRoutines = WorkoutRoutine::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })->where('is_active', 1)->count();

        $totalMealPlans = MealPlan::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })->where('is_active', 1)->count();

        // Admin-level metrics
        $monthlyIncome = MembershipPayment::whereHas('user', function ($q) use ($gymId) {
            $q->when($gymId !== 'all', function ($sq) use ($gymId) {
                $sq->where('gym_id', $gymId);
            });
        })
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('amount')
            + ProductSale::when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereMonth('createdAt', Carbon::now()->month)
            ->sum('total_amount');

        $pendingPaymentsCount = UserMembership::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })->where('payment_status', 'pending')->count();

        $lowStockCount = InventoryProduct::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })->whereRaw('stock_quantity <= min_stock')->count();

        // Superadmin-level global metrics
        $totalGyms = \App\Models\Gym::count();
        $activeGymsCount = \App\Models\Gym::where('is_active', 1)->count();
        $inactiveGymsCount = \App\Models\Gym::where('is_active', 0)->count();
        $totalSystemUsers = User::count();
        $globalSalesTotal = MembershipPayment::sum('amount') + ProductSale::sum('total_amount');

        $systemAlerts = [
            ['type' => 'warning', 'message' => 'Almacenamiento del servidor SSD en 78%.', 'time' => 'Hace 12 min'],
            ['type' => 'info', 'message' => 'Copia de seguridad semanal de la base de datos completada.', 'time' => 'Hace 3 horas'],
            ['type' => 'success', 'message' => 'Pasarela de pagos Stripe & Cash en línea (100% UP).', 'time' => 'Activo'],
        ];

        // ----------------------------------------------------
        // EXECUTIVE KPI HEADER & ATTENDANCE COMPARISONS
        // ----------------------------------------------------
        $activeMembersQuery = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereHas('memberships', function ($q) {
                $q->where('status', 'active');
            });
            
        $activeMembersCount = $activeMembersQuery->count();
        if ($activeMembersCount === 0) {
            $activeMembersCount = User::where('role', 'member')
                ->when($gymId !== 'all', function ($q) use ($gymId) {
                    $q->where('gym_id', $gymId);
                })->count();
        }

        $todayCheckinsCount = AttendanceLog::whereDate('check_in', Carbon::today())
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })->count();

        $todayParticipationPct = ($activeMembersCount > 0) ? round(($todayCheckinsCount / $activeMembersCount) * 100, 1) : 0;

        $startThisWeek = Carbon::now()->startOfWeek();
        $endThisWeek = Carbon::now()->endOfWeek();
        $startLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endLastWeek = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekCheckinsCount = AttendanceLog::whereBetween('check_in', [$startThisWeek, $endThisWeek])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })->count();

        $lastWeekCheckinsCount = AttendanceLog::whereBetween('check_in', [$startLastWeek, $endLastWeek])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })->count();

        $weeklyGrowthPct = 0;
        if ($lastWeekCheckinsCount > 0) {
            $weeklyGrowthPct = round((($thisWeekCheckinsCount - $lastWeekCheckinsCount) / $lastWeekCheckinsCount) * 100, 1);
        } else if ($thisWeekCheckinsCount > 0) {
            $weeklyGrowthPct = 100;
        }

        // ----------------------------------------------------
        // RETENTION & CHURN RISK CONSOLE
        // ----------------------------------------------------
        // Members with no attendance in last 7 days
        $atRiskMembersCount = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereDoesntHave('attendanceLogs', function($q) {
                $q->where('check_in', '>=', Carbon::now()->subDays(7));
            })->count();

        // Expiring memberships in next 7 days
        $expiringMembershipsCount = UserMembership::where('status', 'active')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->count();

        // Retention rate % (attended in last 14 days)
        $attendedLast14DaysCount = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereHas('attendanceLogs', function($q) {
                $q->where('check_in', '>=', Carbon::now()->subDays(14));
            })->count();

        $retentionRatePct = ($activeMembersCount > 0) ? round(($attendedLast14DaysCount / $activeMembersCount) * 100, 1) : 0;

        // ----------------------------------------------------
        // WEEKLY ATTENDANCE INITIAL DATA (MONDAY - SUNDAY)
        // ----------------------------------------------------
        $attLogsByDay = AttendanceLog::when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereBetween('check_in', [$startThisWeek, $endThisWeek])
            ->selectRaw('DAYOFWEEK(check_in) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $daysMap = [2, 3, 4, 5, 6, 7, 1]; // Mon..Sun
        $daysLabels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $dailyRadarLabels = [];
        $dailyRadarCounts = [];

        foreach ($daysMap as $idx => $dayNum) {
            $dateObj = (clone $startThisWeek)->addDays($idx);
            $dailyRadarLabels[] = $daysLabels[$idx] . ' ' . $dateObj->format('d/m');
            $dailyRadarCounts[] = $attLogsByDay[$dayNum] ?? 0;
        }

        $attendanceData = $dailyRadarCounts;

        // ----------------------------------------------------
        // HOURLY TRAFFIC INITIAL DATA
        // ----------------------------------------------------
        $operatingHours = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];
        
        $rawHourlyCheckins = AttendanceLog::when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereBetween('check_in', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->selectRaw('HOUR(check_in) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $trafficHourLabels = [];
        $trafficHourCounts = [];
        $trafficSaturationColors = [];
        $maxHourlyCount = 1;

        foreach ($operatingHours as $h) {
            $formattedHour = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $count = $rawHourlyCheckins[$h] ?? 0;
            $trafficHourLabels[] = $formattedHour;
            $trafficHourCounts[] = $count;
            if ($count > $maxHourlyCount) {
                $maxHourlyCount = $count;
            }
        }

        foreach ($trafficHourCounts as $count) {
            $saturationRatio = ($maxHourlyCount > 0) ? ($count / $maxHourlyCount) : 0;
            if ($saturationRatio >= 0.75 && $count > 0) {
                $trafficSaturationColors[] = '#f43f5e';
            } elseif ($saturationRatio >= 0.40) {
                $trafficSaturationColors[] = '#f59e0b';
            } else {
                $trafficSaturationColors[] = '#10b981';
            }
        }

        $peakHourIndex = array_search(max($trafficHourCounts), $trafficHourCounts);
        $peakHourVal = max($trafficHourCounts);
        $peakHourText = ($peakHourVal > 0) ? ($trafficHourLabels[$peakHourIndex] . ' - ' . ($operatingHours[$peakHourIndex] + 1) . ':00 hrs (' . $peakHourVal . ' accesos)') : 'Sin registros hoy';

        $nonZeroCounts = array_filter($trafficHourCounts, fn($c) => $c > 0);
        $minHourVal = count($nonZeroCounts) > 0 ? min($nonZeroCounts) : 0;
        $quietHourIndex = array_search($minHourVal, $trafficHourCounts);
        $quietHourText = ($minHourVal > 0) ? ($trafficHourLabels[$quietHourIndex] . ' - ' . ($operatingHours[$quietHourIndex] + 1) . ':00 hrs (' . $minHourVal . ' accesos)') : 'Sin registros hoy';

        $daysOfWeekNames = [
            1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
        ];

        $rawDayCheckins = AttendanceLog::when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->selectRaw('DAYOFWEEK(check_in) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $busiestDayNum = 2;
        $busiestDayMax = 0;
        foreach ($daysOfWeekNames as $dayNum => $dayName) {
            $c = $rawDayCheckins[$dayNum] ?? 0;
            if ($c > $busiestDayMax) {
                $busiestDayMax = $c;
                $busiestDayNum = $dayNum;
            }
        }

        $busiestDayName = ($busiestDayMax > 0) ? ($daysOfWeekNames[$busiestDayNum] . ' (' . $busiestDayMax . ' accesos)') : 'Sin registros';

        // Fetch ALL members for interactive pagination (10 per page)
        $recentClients = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->with(['profile', 'latestMeasurement', 'activeRoutine.routine'])
            ->orderBy('id', 'desc')
            ->get();

        // ----------------------------------------------------
        // 5 NEW OPERATIONAL & ANALYTICAL SECTIONS DATA
        // ----------------------------------------------------
        // 1. Top Classes & Upcoming Schedules
        $topClasses = \App\Models\ClassSchedule::with(['gymClass', 'trainer.user.profile'])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->withCount('bookings')
            ->orderBy('scheduled_date', 'asc')
            ->take(4)
            ->get();

        // 2. Top Selling Products & Inventory POS Status
        $topProducts = \App\Models\InventoryProduct::with('category')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->withCount('saleItems')
            ->orderBy('sale_items_count', 'desc')
            ->take(4)
            ->get();

        // 3. Membership Plan Distribution
        $membershipDistribution = \App\Models\MembershipPlan::when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->withCount(['memberships' => function($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('memberships_count', 'desc')
            ->get();

        // 4. Leaderboard / Top Athletes of the Month
        $topAthletes = \App\Models\UserGamificationStat::with('user.profile')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->orderBy('total_xp', 'desc')
            ->take(4)
            ->get();

        // 5. Today's Training Sessions
        $todaySessions = \App\Models\WorkoutSession::whereDate('session_date', Carbon::today())
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->whereHas('user', function($sq) use ($gymId) {
                    $sq->where('gym_id', $gymId);
                });
            })
            ->with(['user.profile', 'routine'])
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        return view('dashboard', compact(
            'totalClients',
            'activeClientsToday',
            'totalRoutines',
            'totalMealPlans',
            'monthlyIncome',
            'pendingPaymentsCount',
            'lowStockCount',
            'totalGyms',
            'activeGymsCount',
            'inactiveGymsCount',
            'totalSystemUsers',
            'globalSalesTotal',
            'systemAlerts',
            'attendanceData',
            'recentClients',
            'activeMembersCount',
            'todayCheckinsCount',
            'todayParticipationPct',
            'thisWeekCheckinsCount',
            'lastWeekCheckinsCount',
            'weeklyGrowthPct',
            'atRiskMembersCount',
            'expiringMembershipsCount',
            'retentionRatePct',
            'dailyRadarLabels',
            'dailyRadarCounts',
            'trafficHourLabels',
            'trafficHourCounts',
            'trafficSaturationColors',
            'peakHourText',
            'quietHourText',
            'busiestDayName',
            'topClasses',
            'topProducts',
            'membershipDistribution',
            'topAthletes',
            'todaySessions'
        ));
    }

    /**
     * API Endpoint for Weekly Attendance Chart (Interactive AJAX Filter).
     */
    public function apiAttendanceData(Request $request)
    {
        $gymId = $this->getActiveGymId();
        $period = $request->query('period', 'this_week');

        if ($period === 'last_week') {
            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($period === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->query('end_date'))->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        }

        $activeMembersCount = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereHas('memberships', function ($q) {
                $q->where('status', 'active');
            })->count();

        if ($activeMembersCount === 0) {
            $activeMembersCount = User::where('role', 'member')
                ->when($gymId !== 'all', function ($q) use ($gymId) {
                    $q->where('gym_id', $gymId);
                })->count();
        }

        $attendanceRaw = AttendanceLog::whereBetween('check_in', [$startDate, $endDate])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->selectRaw('DATE(check_in) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $counts = [];
        $periodDays = (int)$startDate->diffInDays($endDate) + 1;
        $dateCursor = clone $startDate;

        for ($i = 0; $i < $periodDays; $i++) {
            $dateKey = $dateCursor->format('Y-m-d');
            $labels[] = $dateCursor->isoFormat('D MMM (dd)');
            $counts[] = $attendanceRaw[$dateKey] ?? 0;
            $dateCursor->addDay();
        }

        $totalCheckins = array_sum($counts);
        $maxCount = max($counts) ?: 0;
        $peakIndex = array_search($maxCount, $counts);
        $peakLabel = ($maxCount > 0 && isset($labels[$peakIndex])) ? $labels[$peakIndex] . ' (' . $maxCount . ' asistencias)' : 'Sin registros';
        $avgDaily = $periodDays > 0 ? round($totalCheckins / $periodDays, 1) : 0;

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'counts' => $counts,
            'active_members' => $activeMembersCount,
            'total_checkins' => $totalCheckins,
            'peak_day' => $peakLabel,
            'avg_daily' => $avgDaily,
        ]);
    }

    /**
     * API Endpoint for Hourly Traffic Saturation Chart (Interactive AJAX Filter).
     */
    public function apiTrafficData(Request $request)
    {
        $gymId = $this->getActiveGymId();
        $period = $request->query('period', 'today');

        $query = AttendanceLog::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        });

        if ($period === 'this_week') {
            $query->whereBetween('check_in', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween('check_in', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($period === 'custom' && $request->filled('date')) {
            $query->whereDate('check_in', $request->query('date'));
        } else {
            $query->whereDate('check_in', Carbon::today());
        }

        $rawHourly = (clone $query)
            ->selectRaw('HOUR(check_in) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $operatingHours = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];
        $labels = [];
        $counts = [];
        $colors = [];
        $maxHourlyCount = 1;

        foreach ($operatingHours as $h) {
            $formattedHour = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $count = $rawHourly[$h] ?? 0;
            $labels[] = $formattedHour;
            $counts[] = $count;
            if ($count > $maxHourlyCount) {
                $maxHourlyCount = $count;
            }
        }

        foreach ($counts as $count) {
            $ratio = ($maxHourlyCount > 0) ? ($count / $maxHourlyCount) : 0;
            if ($ratio >= 0.75 && $count > 0) {
                $colors[] = '#f43f5e';
            } elseif ($ratio >= 0.40) {
                $colors[] = '#f59e0b';
            } else {
                $colors[] = '#10b981';
            }
        }

        $peakIndex = array_search(max($counts), $counts);
        $peakVal = max($counts);
        $peakText = ($peakVal > 0) ? ($labels[$peakIndex] . ' - ' . ($operatingHours[$peakIndex] + 1) . ':00 hrs (' . $peakVal . ' accesos)') : 'Sin registros';

        $nonZeroCounts = array_filter($counts, fn($c) => $c > 0);
        $minVal = count($nonZeroCounts) > 0 ? min($nonZeroCounts) : 0;
        $quietIndex = array_search($minVal, $counts);
        $quietText = ($minVal > 0) ? ($labels[$quietIndex] . ' - ' . ($operatingHours[$quietIndex] + 1) . ':00 hrs (' . $minVal . ' accesos)') : 'Sin registros';

        $daysOfWeekNames = [
            1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
        ];

        $rawDayCheckins = (clone $query)
            ->selectRaw('DAYOFWEEK(check_in) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $busiestDayNum = 2;
        $busiestMax = 0;
        foreach ($daysOfWeekNames as $dayNum => $dayName) {
            $c = $rawDayCheckins[$dayNum] ?? 0;
            if ($c > $busiestMax) {
                $busiestMax = $c;
                $busiestDayNum = $dayNum;
            }
        }
        $busiestDayName = ($busiestMax > 0) ? ($daysOfWeekNames[$busiestDayNum] . ' (' . $busiestMax . ' accesos)') : 'Sin registros';

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'counts' => $counts,
            'colors' => $colors,
            'peak_hour' => $peakText,
            'quiet_hour' => $quietText,
            'busiest_day' => $busiestDayName,
            'total_period' => array_sum($counts),
        ]);
    }

    /**
     * Mis Clientes directory.
     */
    public function clientes()
    {
        $gymId = $this->getActiveGymId();

        $query = User::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })
            ->with(['profile', 'latestMeasurement', 'activeRoutine.routine', 'activeMealPlan.mealPlan']);

        if (auth()->user()->role !== 'superadmin') {
            $query->where('role', 'member');
        }

        $clientes = $query->get();

        return view('clientes', compact('clientes'));
    }

    /**
     * View specific client profile.
     */
    public function showCliente($id)
    {
        $gymId = $this->getActiveGymId();

        $cliente = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->with([
                'profile',
                'bodyMeasurements' => function ($q) {
                    $q->orderBy('measured_at', 'asc');
                },
                'latestMeasurement',
                'activeRoutine.routine',
                'activeRoutine.assigner',
                'activeMealPlan.mealPlan',
                'activeMealPlan.assigner',
                'activeMembership.plan',
                'memberships.plan',
                'membershipPayments',
                'activeTrainerAssignment.trainer'
            ])
            ->findOrFail($id);

        // Format weight history chart
        $measurements = $cliente->bodyMeasurements;
        $weightPoints = "";
        $weightPolygonPoints = "";
        $weightDates = [];
        $weightValues = [];

        if ($measurements->count() > 0) {
            $minWeight = $measurements->min('weight_kg') - 2;
            $maxWeight = $measurements->max('weight_kg') + 2;
            $weightRange = $maxWeight - $minWeight ?: 1;

            $pts = [];
            $polyPts = [];

            if ($measurements->count() === 1) {
                $m = $measurements->first();
                $x = 300;
                $y = 100;
                $pts[] = "$x,$y";
                $polyPts = ["30,200", "30,100", "570,100", "570,200"];
                $weightDates[] = Carbon::parse($m->measured_at)->format('d/m/Y');
                $weightValues[] = $m->weight_kg;
            } else {
                $xStep = (540 / ($measurements->count() - 1));
                $polyPts[] = "30,200";

                foreach ($measurements as $index => $m) {
                    $x = 30 + ($index * $xStep);
                    $y = 180 - ((($m->weight_kg - $minWeight) / $weightRange) * 140);
                    $pts[] = "$x,$y";
                    $polyPts[] = "$x,$y";
                    $weightDates[] = Carbon::parse($m->measured_at)->format('d/m');
                    $weightValues[] = $m->weight_kg;
                }
                $polyPts[] = "570,200";
            }

            $weightPoints = implode(' ', $pts);
            $weightPolygonPoints = implode(' ', $polyPts);
        }

        // Fetch routines & meal plans for assignment modals
        $routines = WorkoutRoutine::where(function($q) use ($cliente) {
            if ($cliente->gym_id) {
                $q->where('gym_id', $cliente->gym_id)->orWhereNull('gym_id');
            }
        })->where('is_active', 1)->get();

        $mealPlans = MealPlan::where(function($q) use ($cliente) {
            if ($cliente->gym_id) {
                $q->where('gym_id', $cliente->gym_id)->orWhereNull('gym_id');
            }
        })->where('is_active', 1)->get();

        $trainers = Trainer::when($cliente->gym_id, function($q) use ($cliente) {
            $q->where('gym_id', $cliente->gym_id);
        })->where('is_active', 1)->get();

        return view('clientes.show', compact(
            'cliente',
            'weightPoints',
            'weightPolygonPoints',
            'weightDates',
            'weightValues',
            'routines',
            'mealPlans',
            'trainers'
        ));
    }

    /**
     * Create client form.
     */
    public function crearCliente()
    {
        return view('clientes.crear');
    }

    /**
     * Consultar CNE por número de DNI / Cédula.
     */
    public function consultarCne(Request $request)
    {
        $rawDni = trim($request->input('dni', $request->input('cedula', '')));
        if (empty($rawDni)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingresa un número de DNI o cédula.'
            ], 422);
        }

        // Extract nationality prefix if present (e.g. V-12345678, E-12345678, V12345678)
        $nacionalidad = 'V';
        $cedula = $rawDni;

        if (preg_match('/^([VEve])[-_\s]*(\d+)$/', $rawDni, $matches)) {
            $nacionalidad = strtoupper($matches[1]);
            $cedula = $matches[2];
        } else {
            // Strip any non-digit characters
            $cedula = preg_replace('/\D/', '', $rawDni);
        }

        if (empty($cedula)) {
            return response()->json([
                'success' => false,
                'message' => 'Número de cédula inválido.'
            ], 422);
        }

        $appId = env('CNE_API_APP_ID', '2118');
        $token = env('CNE_API_TOKEN', 'ad3e6e46e42e96adba76c92c23755b54');

        try {
            $response = Http::withoutVerifying()->timeout(10)->get('https://api.cedula.com.ve/api/v1', [
                'app_id' => $appId,
                'token' => $token,
                'nacionalidad' => $nacionalidad,
                'cedula' => $cedula,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de conexión con el servicio CNE.'
                ], 502);
            }

            $data = $response->json();

            if (!isset($data['error']) || $data['error'] === true || empty($data['data'])) {
                $errorStr = $data['error_str'] ?? 'No se encontraron datos para la cédula ingresada.';
                return response()->json([
                    'success' => false,
                    'message' => is_string($errorStr) ? $errorStr : 'Cédula no encontrada en la base de datos CNE.'
                ], 404);
            }

            $info = $data['data'];
            $primerNombre = trim($info['primer_nombre'] ?? '');
            $segundoNombre = trim($info['segundo_nombre'] ?? '');
            $primerApellido = trim($info['primer_apellido'] ?? '');
            $segundoApellido = trim($info['segundo_apellido'] ?? '');

            $firstName = trim($primerNombre . ' ' . $segundoNombre);
            $lastName = trim($primerApellido . ' ' . $segundoApellido);

            // Format nicely (Title Case) if string is uppercase
            if (mb_strtoupper($firstName) === $firstName) {
                $firstName = mb_convert_case($firstName, MB_CASE_TITLE, 'UTF-8');
            }
            if (mb_strtoupper($lastName) === $lastName) {
                $lastName = mb_convert_case($lastName, MB_CASE_TITLE, 'UTF-8');
            }

            return response()->json([
                'success' => true,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'data' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción al consultar el servicio CNE: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new client.
     */
    public function storeCliente(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'dni' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'profile_photo' => 'nullable|url',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withInput()->withErrors(['gym' => 'Debes seleccionar una sucursal específica para poder registrar un cliente.']);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Create User
            $user = User::create([
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'member',
                'is_active' => 1,
                'email_verified' => 0,
                'gym_id' => $gymId,
            ]);

            // Create UserProfile
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'profile_photo' => $request->profile_photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop',
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $errorMessage = $e->getMessage();

            // Check if it's a trigger exception (SQLSTATE 45000)
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al registrar el cliente. Verifique los límites de su plan.';
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Assign Workout Routine to client.
     */
    public function assignRoutine(Request $request, $id)
    {
        $request->validate([
            'routine_id' => 'required|exists:workout_routines,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $routine = WorkoutRoutine::findOrFail($request->routine_id);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        $startDate = Carbon::parse($request->start_date);
        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date);
        } elseif ($routine->duration_weeks) {
            $endDate = $startDate->copy()->addWeeks((int)$routine->duration_weeks);
        } else {
            $endDate = null;
        }

        // Deactivate existing assignments
        UserAssignedRoutine::where('user_id', $id)->update(['is_active' => 0]);

        // Create assignment
        UserAssignedRoutine::create([
            'user_id' => $id,
            'routine_id' => $request->routine_id,
            'assigned_by' => $trainerId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate ? $endDate->toDateString() : null,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Rutina asignada exitosamente.');
    }

    /**
     * Assign Meal Plan to client.
     */
    public function assignMealPlan(Request $request, $id)
    {
        $request->validate([
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'start_date' => 'required|date',
        ]);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        // Deactivate existing assignments
        UserMealPlan::where('user_id', $id)->update(['is_active' => 0]);

        // Create assignment
        UserMealPlan::create([
            'user_id' => $id,
            'meal_plan_id' => $request->meal_plan_id,
            'assigned_by' => $trainerId,
            'start_date' => $request->start_date,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Plan de nutrición asignado exitosamente.');
    }

    /**
     * Planes de Rutinas.
     */
    public function rutinas()
    {
        $gymId = $this->getActiveGymId();

        $rutinas = WorkoutRoutine::where('gym_id', $gymId)
            ->withCount(['assignments as active_assignments_count' => function ($q) {
                $q->where('is_active', 1);
            }])->get();

        $totalClients = User::where('role', 'member')->where('gym_id', $gymId)->count();

        $activeAssignmentsCount = UserAssignedRoutine::where('is_active', 1)
            ->whereHas('user', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })->count();

        $popularRoutine = WorkoutRoutine::where('gym_id', $gymId)
            ->withCount(['assignments' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->orderBy('assignments_count', 'desc')
            ->first();
        $popularRoutineName = $popularRoutine ? $popularRoutine->name : 'N/A';

        $clientes = User::where('role', 'member')->where('gym_id', $gymId)->with('profile')->get();

        return view('rutinas', compact('rutinas', 'totalClients', 'activeAssignmentsCount', 'popularRoutineName', 'clientes'));
    }

    /**
     * Create routine form.
     */
    public function crearRutina()
    {
        return view('rutinas.crear');
    }

    /**
     * Store new routine template.
     */
    public function storeRutina(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,improve_flexibility',
            'bmi_category' => 'required|in:all,underweight,normal,overweight,obese',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_weeks' => 'required|integer|min:1',
            'days_per_week' => 'required|integer|min:1|max:7',
        ]);

        $gymId = auth()->user()->gym_id;
        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        WorkoutRoutine::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'goal_type' => $request->goal_type,
            'bmi_category' => $request->bmi_category,
            'difficulty' => $request->difficulty,
            'duration_weeks' => $request->duration_weeks,
            'days_per_week' => $request->days_per_week,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
            'is_active' => 1,
            'created_by' => $trainerId,
        ]);

        return redirect()->route('rutinas.index')->with('success', 'Plan de rutina creado con éxito.');
    }

    /**
     * Update a workout routine template information.
     */
    public function updateRutina(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,improve_flexibility',
            'bmi_category' => 'required|in:all,underweight,normal,overweight,obese',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_weeks' => 'required|integer|min:1',
            'days_per_week' => 'required|integer|min:1|max:7',
        ]);

        $gymId = $this->getActiveGymId();
        $routine = WorkoutRoutine::where('gym_id', $gymId)->findOrFail($id);

        $routine->update([
            'name' => $request->name,
            'description' => $request->description,
            'goal_type' => $request->goal_type,
            'bmi_category' => $request->bmi_category,
            'difficulty' => $request->difficulty,
            'duration_weeks' => $request->duration_weeks,
            'days_per_week' => $request->days_per_week,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Sync days if days_per_week increased or decreased
        $currentDaysCount = $routine->days()->count();
        if ($routine->days_per_week > $currentDaysCount) {
            for ($i = $currentDaysCount + 1; $i <= $routine->days_per_week; $i++) {
                RoutineDay::create([
                    'routine_id' => $routine->id,
                    'day_number' => $i,
                    'day_name' => "Día $i: Entrenamiento",
                    'focus_area' => 'Fuerza General'
                ]);
            }
        } elseif ($routine->days_per_week < $currentDaysCount) {
            $routine->days()->where('day_number', '>', $routine->days_per_week)->delete();
        }

        $message = 'Información de la rutina actualizada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            $routine->load('days.exercises.exercise');
            return response()->json([
                'success' => true,
                'message' => $message,
                'routine' => $routine
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Planes de Nutrición.
     */
    public function nutricion()
    {
        $gymId = $this->getActiveGymId();

        $dietas = MealPlan::where('gym_id', $gymId)
            ->withCount(['assignments as active_assignments_count' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->with(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner'])
            ->get();

        $clientes = User::where('role', 'member')->where('gym_id', $gymId)->with('profile')->get();

        return view('nutricion', compact('dietas', 'clientes'));
    }

    /**
     * Create nutrition plan form.
     */
    public function crearNutricion()
    {
        return view('nutricion.crear');
    }

    /**
     * Store new nutrition plan template.
     */
    public function storeNutricion(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,general',
            'bmi_category' => 'required|in:all,underweight,normal,overweight,obese',
            'duration_weeks' => 'required|integer|min:1',
            'daily_calories' => 'required|numeric|min:500|max:10000',
        ]);

        $gymId = auth()->user()->gym_id;

        MealPlan::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'goal_type' => $request->goal_type,
            'bmi_category' => $request->bmi_category,
            'duration_weeks' => $request->duration_weeks,
            'daily_calories' => $request->daily_calories,
            'is_active' => 1,
        ]);

        return redirect()->route('nutricion.index')->with('success', 'Plan de nutrición creado con éxito.');
    }

    /**
     * Show meals schedule for a plan.
     */
    public function showComidas($id)
    {
        $gymId = $this->getActiveGymId();

        $plan = MealPlan::where('gym_id', $gymId)
            ->with([
                'days.breakfast.ingredients', 'days.breakfast.category',
                'days.snack1.ingredients', 'days.snack1.category',
                'days.lunch.ingredients', 'days.lunch.category',
                'days.snack2.ingredients', 'days.snack2.category',
                'days.dinner.ingredients', 'days.dinner.category',
            ])
            ->findOrFail($id);

        $recipes = Recipe::where('gym_id', $gymId)
            ->orWhereNull('gym_id')
            ->with(['category', 'ingredients'])
            ->orderBy('name')
            ->get();

        return view('nutricion.comidas', compact('plan', 'recipes'));
    }

    /**
     * Update a nutrition meal plan template information.
     */
    public function updateNutricion(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,general',
            'bmi_category' => 'required|in:all,underweight,normal,overweight,obese',
            'duration_weeks' => 'required|integer|min:1',
            'daily_calories' => 'required|numeric|min:500|max:10000',
        ]);

        $gymId = $this->getActiveGymId();
        $plan = MealPlan::where('gym_id', $gymId)->findOrFail($id);

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'goal_type' => $request->goal_type,
            'bmi_category' => $request->bmi_category,
            'duration_weeks' => $request->duration_weeks,
            'daily_calories' => $request->daily_calories,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        $message = 'Información del plan de nutrición actualizada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            $plan->load(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner']);
            return response()->json([
                'success' => true,
                'message' => $message,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Add a new day to a meal plan.
     */
    public function addMealPlanDay(Request $request, $id)
    {
        $gymId = $this->getActiveGymId();
        $plan = MealPlan::where('gym_id', $gymId)->findOrFail($id);

        $maxDay = MealPlanDay::where('meal_plan_id', $plan->id)->max('day_number') ?? 0;
        $newDayNumber = $maxDay + 1;

        $newDay = MealPlanDay::create([
            'meal_plan_id' => $plan->id,
            'day_number' => $newDayNumber,
        ]);

        $message = "Día {$newDayNumber} añadido exitosamente al plan.";

        if ($request->ajax() || $request->wantsJson()) {
            $plan->load(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner']);
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_day' => $newDay,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Save/Update meals for a specific day in a meal plan.
     */
    public function saveComidasDay(Request $request, $id)
    {
        $request->validate([
            'day_number' => 'required|integer|min:1',
            'breakfast_recipe_id' => 'nullable|exists:recipes,id',
            'snack1_recipe_id' => 'nullable|exists:recipes,id',
            'lunch_recipe_id' => 'nullable|exists:recipes,id',
            'snack2_recipe_id' => 'nullable|exists:recipes,id',
            'dinner_recipe_id' => 'nullable|exists:recipes,id',
        ]);

        $gymId = $this->getActiveGymId();
        $plan = MealPlan::where('gym_id', $gymId)->findOrFail($id);

        $day = MealPlanDay::where('meal_plan_id', $plan->id)
            ->where('day_number', $request->day_number)
            ->firstOrFail();

        $day->update([
            'breakfast_recipe_id' => $request->breakfast_recipe_id ?: null,
            'snack1_recipe_id' => $request->snack1_recipe_id ?: null,
            'lunch_recipe_id' => $request->lunch_recipe_id ?: null,
            'snack2_recipe_id' => $request->snack2_recipe_id ?: null,
            'dinner_recipe_id' => $request->dinner_recipe_id ?: null,
        ]);

        $message = "Menú del Día {$day->day_number} actualizado con éxito.";

        if ($request->ajax() || $request->wantsJson()) {
            $day->load(['breakfast', 'snack1', 'lunch', 'snack2', 'dinner']);
            $plan->load(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner']);
            return response()->json([
                'success' => true,
                'message' => $message,
                'day' => $day,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete a day from a meal plan.
     */
    public function deleteMealPlanDay($id, $day_id)
    {
        $gymId = $this->getActiveGymId();
        $plan = MealPlan::where('gym_id', $gymId)->findOrFail($id);

        $day = MealPlanDay::where('meal_plan_id', $plan->id)->findOrFail($day_id);
        $deletedDayNumber = $day->day_number;
        $day->delete();

        // Re-index remaining days
        $remainingDays = MealPlanDay::where('meal_plan_id', $plan->id)->orderBy('day_number', 'asc')->get();
        foreach ($remainingDays as $idx => $d) {
            $d->update(['day_number' => $idx + 1]);
        }

        $message = "Día {$deletedDayNumber} eliminado del plan.";

        if (request()->ajax() || request()->wantsJson()) {
            $plan->load(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner']);
            return response()->json([
                'success' => true,
                'message' => $message,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove a single recipe slot from a day.
     */
    public function removeMealFromDay(Request $request, $id, $day_id)
    {
        $request->validate([
            'meal_type' => 'required|in:breakfast,snack1,lunch,snack2,dinner',
        ]);

        $gymId = $this->getActiveGymId();
        $plan = MealPlan::where('gym_id', $gymId)->findOrFail($id);

        $day = MealPlanDay::where('meal_plan_id', $plan->id)->findOrFail($day_id);
        $mealField = $request->meal_type . '_recipe_id';
        $day->{$mealField} = null;
        $day->save();

        $mealLabels = [
            'breakfast' => 'Desayuno',
            'snack1' => 'Media Mañana',
            'lunch' => 'Almuerzo / Comida',
            'snack2' => 'Media Tarde',
            'dinner' => 'Cena'
        ];
        $mealLabel = $mealLabels[$request->meal_type] ?? $request->meal_type;
        $message = "Comida de '{$mealLabel}' quitada del Día {$day->day_number}.";

        if ($request->ajax() || $request->wantsJson()) {
            $day->load(['breakfast', 'snack1', 'lunch', 'snack2', 'dinner']);
            $plan->load(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner']);
            return response()->json([
                'success' => true,
                'message' => $message,
                'day' => $day,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Edit exercises inside a workout routine template.
     */
    public function editEjercicios($id)
    {
        $gymId = $this->getActiveGymId();

        $routine = WorkoutRoutine::where('gym_id', $gymId)
            ->with(['days.exercises.exercise.equipment', 'days.exercises.exercise.category'])
            ->findOrFail($id);

        // Auto-initialize days if not created yet
        if ($routine->days->count() === 0) {
            for ($i = 1; $i <= $routine->days_per_week; $i++) {
                RoutineDay::create([
                    'routine_id' => $routine->id,
                    'day_number' => $i,
                    'day_name' => "Día $i: Entrenamiento",
                    'focus_area' => 'Fuerza General'
                ]);
            }
            $routine = WorkoutRoutine::where('gym_id', $gymId)
                ->with(['days.exercises.exercise.equipment', 'days.exercises.exercise.category'])
                ->findOrFail($id);
        }

        $exercises = Exercise::where('gym_id', $gymId)
            ->orWhereNull('gym_id')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('rutinas.ejercicios', compact('routine', 'exercises'));
    }

    /**
     * Add an exercise to a routine day.
     */
    public function addEjercicio(Request $request, $id)
    {
        $request->validate([
            'routine_day_id' => 'required|exists:routine_days,id',
            'exercise_id' => 'required|exists:exercises,id',
            'sets' => 'required|integer|min:1',
            'reps' => 'required|string|max:50',
            'rest_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $gymId = $this->getActiveGymId();
        $routine = WorkoutRoutine::where('gym_id', $gymId)->findOrFail($id);
        $day = RoutineDay::where('routine_id', $routine->id)->findOrFail($request->routine_day_id);

        $maxOrder = RoutineExercise::where('routine_day_id', $day->id)->max('order_index') ?? 0;

        $routineExercise = RoutineExercise::create([
            'routine_day_id' => $day->id,
            'exercise_id' => $request->exercise_id,
            'sets' => $request->sets,
            'reps' => $request->reps,
            'rest_seconds' => $request->rest_seconds ?? 60,
            'order_index' => $maxOrder + 1,
            'notes' => $request->notes,
        ]);

        $message = 'Ejercicio añadido exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            $routineExercise->load('exercise');
            return response()->json([
                'success' => true,
                'message' => $message,
                'routine_exercise' => $routineExercise
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an assigned exercise.
     */
    public function updateEjercicio(Request $request, $id, $routine_exercise_id)
    {
        $request->validate([
            'sets' => 'required|integer|min:1',
            'reps' => 'required|string|max:50',
            'rest_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $ex = RoutineExercise::findOrFail($routine_exercise_id);
        $ex->update([
            'sets' => $request->sets,
            'reps' => $request->reps,
            'rest_seconds' => $request->rest_seconds ?? 60,
            'notes' => $request->notes,
        ]);

        $message = 'Ejercicio actualizado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            $ex->load('exercise');
            return response()->json([
                'success' => true,
                'message' => $message,
                'routine_exercise' => $ex
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove an exercise from a day.
     */
    public function removeEjercicio($id, $routine_exercise_id)
    {
        $ex = RoutineExercise::findOrFail($routine_exercise_id);
        $ex->delete();

        $message = 'Ejercicio removido del día.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Assign routine from routines list to a user.
     */
    public function assignRoutineToUser(Request $request, $routine_id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $routine = WorkoutRoutine::findOrFail($routine_id);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        $startDate = Carbon::parse($request->start_date);
        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date);
        } elseif ($routine->duration_weeks) {
            $endDate = $startDate->copy()->addWeeks((int)$routine->duration_weeks);
        } else {
            $endDate = null;
        }

        UserAssignedRoutine::where('user_id', $request->user_id)->update(['is_active' => 0]);

        UserAssignedRoutine::create([
            'user_id' => $request->user_id,
            'routine_id' => $routine_id,
            'assigned_by' => $trainerId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate ? $endDate->toDateString() : null,
            'is_active' => 1,
        ]);

        $message = 'Rutina asignada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'routine_id' => $routine_id,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Assign meal plan from nutrition list to a user.
     */
    public function assignMealPlanToUser(Request $request, $meal_plan_id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
        ]);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        UserMealPlan::where('user_id', $request->user_id)->update(['is_active' => 0]);

        UserMealPlan::create([
            'user_id' => $request->user_id,
            'meal_plan_id' => $meal_plan_id,
            'assigned_by' => $trainerId,
            'start_date' => $request->start_date,
            'is_active' => 1,
        ]);

        $message = 'Plan de nutrición asignado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            $activeAssignmentsCount = UserMealPlan::where('meal_plan_id', $meal_plan_id)->where('is_active', 1)->count();
            return response()->json([
                'success' => true,
                'message' => $message,
                'meal_plan_id' => $meal_plan_id,
                'active_assignments_count' => $activeAssignmentsCount
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Search clients, routines, and meal plans scoped by the current gym context.
     */
    public function globalSearch(Request $request)
    {
        $queryStr = $request->input('q');
        $gymId = $this->getActiveGymId();

        if ($gymId === 'all') {
            $activeGymName = 'Todas las Sucursales';
        } else {
            if ($gymId == auth()->user()->gym_id) {
                $activeGymName = auth()->user()->gym->name;
            } else {
                $activeGymName = \App\Models\Gym::where('id', $gymId)->value('name') ?? 'Vista General';
            }
        }

        if (empty($queryStr)) {
            return redirect()->route('dashboard');
        }

        // Search Clients
        $clientes = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function ($q) use ($queryStr) {
                $q->where('email', 'like', "%{$queryStr}%")
                    ->orWhereHas('profile', function ($pq) use ($queryStr) {
                        $pq->where('first_name', 'like', "%{$queryStr}%")
                            ->orWhere('last_name', 'like', "%{$queryStr}%")
                            ->orWhere('phone', 'like', "%{$queryStr}%");
                    });
            })
            ->with(['profile', 'gym'])
            ->take(20)
            ->get();

        // Search Workout Routines
        $rutinas = WorkoutRoutine::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })
            ->where(function ($q) use ($queryStr) {
                $q->where('name', 'like', "%{$queryStr}%")
                    ->orWhere('description', 'like', "%{$queryStr}%")
                    ->orWhere('goal_type', 'like', "%{$queryStr}%");
            })
            ->with('gym')
            ->take(20)
            ->get();

        // Search Meal Plans (Dietas)
        $dietas = MealPlan::when($gymId !== 'all', function ($q) use ($gymId) {
            $q->where('gym_id', $gymId);
        })
            ->where(function ($q) use ($queryStr) {
                $q->where('name', 'like', "%{$queryStr}%")
                    ->orWhere('description', 'like', "%{$queryStr}%")
                    ->orWhere('goal_type', 'like', "%{$queryStr}%");
            })
            ->with('gym')
            ->take(20)
            ->get();

        return view('search_results', compact('clientes', 'rutinas', 'dietas', 'queryStr', 'activeGymName'));
    }

    /**
     * Live search for autocompletion (members and trainers).
     */
    public function liveSearch(Request $request)
    {
        $queryStr = $request->input('q');
        $gymId = $this->getActiveGymId();

        if (empty($queryStr) || strlen($queryStr) < 2) {
            return response()->json([]);
        }

        $users = User::whereIn('role', ['member', 'trainer'])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function ($q) use ($queryStr) {
                $q->where('email', 'like', "%{$queryStr}%")
                    ->orWhereHas('profile', function ($pq) use ($queryStr) {
                        $pq->where('first_name', 'like', "%{$queryStr}%")
                            ->orWhere('last_name', 'like', "%{$queryStr}%");
                    });
            })
            ->with(['profile', 'gym'])
            ->take(5)
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => ($user->profile->first_name ?? 'Usuario') . ' ' . ($user->profile->last_name ?? ''),
                'email' => $user->email,
                'role' => $user->role,
                'photo' => $user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
                'gym_name' => $user->gym->name ?? 'N/A',
                'url' => $user->role === 'member' ? route('clientes.show', $user->id) : route('staff.index'),
            ];
        });

        return response()->json($results);
    }

    /**
     * Get unread notifications for the header bell dropdown.
     */
    public function getUnreadNotifications()
    {
        $userId = auth()->id();
        $unreadCount = Notification::where('user_id', $userId)->where('is_read', 0)->count();
        $latestNotifications = Notification::where('user_id', $userId)
            ->orderBy('createdAt', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $latestNotifications,
        ]);
    }

    /**
     * Mark a notification as read and redirect based on type.
     */
    public function readAndRedirect($id)
    {
        $userId = auth()->id();
        $notification = Notification::where('user_id', $userId)->findOrFail($id);
        
        $notification->update(['is_read' => 1]);

        switch ($notification->type) {
            case 'membership_expiry':
                return redirect()->route('clientes.index');
            case 'payment_reminder':
                return redirect()->route('finanzas.index');
            case 'new_routine':
                return redirect()->route('rutinas.index');
            case 'general':
                if (auth()->user()->role === 'superadmin') {
                    return redirect()->route('superadmin.gyms.index');
                }
                return redirect()->route('notificaciones.index');
            default:
                return redirect()->route('notificaciones.index');
        }
    }

    /**
     * Mark all notifications of the active user as read.
     */
    public function markAllAsRead()
    {
        $userId = auth()->id();
        Notification::where('user_id', $userId)->where('is_read', 0)->update(['is_read' => 1]);

        return redirect()->back()->with('success', 'Todas las notificaciones se han marcado como leídas.');
    }

    /**
     * Display notifications history view.
     */
    public function notificationsHistory()
    {
        $userId = auth()->id();
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('createdAt', 'desc')
            ->paginate(15);

        return view('notificaciones.index', compact('notifications'));
    }

    /**
     * Assign a trainer to a client.
     */
    public function assignTrainer(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden asignar entrenadores.');
        }
        $gymId = $this->getActiveGymId();
        $cliente = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->findOrFail($id);

        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $trainer = Trainer::where('gym_id', $cliente->gym_id)->findOrFail($request->trainer_id);

        try {
            DB::beginTransaction();

            // Deactivate any existing active trainer assignments for this client
            UserTrainerAssignment::where('user_id', $cliente->id)
                ->where('is_active', 1)
                ->update([
                    'is_active' => 0, 
                    'end_date' => Carbon::today()
                ]);

            // Create new trainer assignment
            UserTrainerAssignment::create([
                'user_id' => $cliente->id,
                'trainer_id' => $trainer->id,
                'assigned_at' => Carbon::now(),
                'is_active' => 1,
                'notes' => $request->notes,
            ]);

            // Notify the trainer
            Notification::create([
                'user_id' => $trainer->user_id,
                'title' => 'Nuevo socio asignado',
                'body' => 'Se te ha asignado como entrenador personal del socio: ' . ($cliente->profile->first_name ?? '') . ' ' . ($cliente->profile->last_name ?? ''),
                'type' => 'new_routine',
                'is_read' => 0,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Entrenador asignado exitosamente al cliente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if capacity trigger threw an error (SQLSTATE 45000)
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error al asignar entrenador: ' . $errorMessage;
            }
            return redirect()->back()->withErrors(['error' => $errorText]);
        }
    }

    /**
     * Get real-time gym capacity (Aforo) data for AJAX updates.
     */
    public function getAforoApi(Request $request)
    {
        try {
            if ($request->has('gym_id') && !empty($request->input('gym_id'))) {
                $activeGymId = $request->input('gym_id');
            } else {
                $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
            }

            if ($activeGymId === 'all') {
                $currentUsers = User::where('role', 'member')->count();
                $allGyms = Gym::with('plan')->get();
                $maxUsers = 0;
                foreach ($allGyms as $g) {
                    $maxUsers += ($g->plan?->max_users ?? 50);
                }
            } else {
                $currentUsers = User::where('gym_id', $activeGymId)->where('role', 'member')->count();
                $gym = Gym::with('plan')->find($activeGymId);
                $maxUsers = ($gym && $gym->plan) ? ($gym->plan->max_users ?? 50) : 50;
            }

            $percentage = $maxUsers > 0 ? round(($currentUsers / $maxUsers) * 100, 1) : 0;
            $pctFormatted = (floor($percentage) == $percentage) ? (int)$percentage : $percentage;

            $colorClass = 'text-lime-400';
            $badgeBgClass = 'bg-lime-500/10';
            $badgeBorderClass = 'border-lime-500/20';
            $gradientClass = 'from-lime-500 to-emerald-400';

            if ($percentage >= 90) {
                $colorClass = 'text-rose-400';
                $badgeBgClass = 'bg-rose-500/10';
                $badgeBorderClass = 'border-rose-500/20';
                $gradientClass = 'from-rose-500 to-red-500';
            } elseif ($percentage >= 75) {
                $colorClass = 'text-amber-400';
                $badgeBgClass = 'bg-amber-500/10';
                $badgeBorderClass = 'border-amber-500/20';
                $gradientClass = 'from-amber-500 to-yellow-400';
            }

            return response()->json([
                'current' => $currentUsers,
                'max' => $maxUsers,
                'percentage' => $percentage,
                'percentage_formatted' => $pctFormatted,
                'count_text' => "{$currentUsers}/{$maxUsers}",
                'pct_text' => "{$pctFormatted}%",
                'text' => "{$currentUsers}/{$maxUsers} ({$pctFormatted}%)",
                'color_class' => $colorClass,
                'badge_bg_class' => $badgeBgClass,
                'badge_border_class' => $badgeBorderClass,
                'gradient_class' => $gradientClass,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Aforo API error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'current' => 0,
                'max' => 50,
                'percentage' => 0,
                'percentage_formatted' => 0,
                'count_text' => "0/50",
                'pct_text' => "0%",
                'text' => "0/50 (0%)",
                'color_class' => 'text-lime-400',
                'badge_bg_class' => 'bg-lime-500/10',
                'badge_border_class' => 'border-lime-500/20',
                'gradient_class' => 'from-lime-500 to-emerald-400',
            ], 200);
        }
    }
}
