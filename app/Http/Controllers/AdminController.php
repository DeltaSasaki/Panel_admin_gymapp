<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Trainer;
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
use App\Models\ProductSale;
use App\Models\UserMembership;
use App\Models\InventoryProduct;

class AdminController extends Controller
{
    /**
     * Dashboard view.
     */
    public function dashboard()
    {
        $gymId = $this->getActiveGymId();
        
        $totalClients = User::where('role', 'member')
            ->when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })
            ->count();

        $activeClientsToday = WorkoutSession::whereHas('user', function($q) use ($gymId) {
                $q->when($gymId !== 'all', function($sq) use ($gymId) { $sq->where('gym_id', $gymId); });
            })->whereDate('session_date', Carbon::today())->count();
        
        $totalRoutines = WorkoutRoutine::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })->where('is_active', 1)->count();
        $totalMealPlans = MealPlan::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })->where('is_active', 1)->count();

        // Admin-level metrics
        $monthlyIncome = MembershipPayment::whereHas('membership', function($q) use ($gymId) {
                $q->when($gymId !== 'all', function($sq) use ($gymId) { $sq->where('gym_id', $gymId); });
            })
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('amount') 
            + ProductSale::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })
            ->whereMonth('createdAt', Carbon::now()->month)
            ->sum('total_amount');

        $pendingPaymentsCount = UserMembership::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })->where('payment_status', 'pending')->count();
        $lowStockCount = InventoryProduct::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })->whereRaw('stock_quantity <= min_stock')->count();

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

        // Weekly attendance parsing
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sessionsByDay = WorkoutSession::whereHas('user', function($q) use ($gymId) {
                $q->when($gymId !== 'all', function($sq) use ($gymId) { $sq->where('gym_id', $gymId); });
            })
            ->whereBetween('session_date', [$startOfWeek, $endOfWeek])
            ->selectRaw('DAYOFWEEK(session_date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $daysMap = [2, 3, 4, 5, 6, 7, 1]; 
        $attendanceData = [];
        foreach ($daysMap as $dayNum) {
            $attendanceData[] = $sessionsByDay[$dayNum] ?? 0;
        }

        $maxVal = max($attendanceData) ?: 1;
        $xCoords = [30, 120, 210, 300, 390, 480, 570];
        $linePoints = [];
        $polygonPoints = ["30,200"];

        foreach ($attendanceData as $index => $count) {
            $x = $xCoords[$index];
            $y = 180 - (($count / $maxVal) * 145);
            $linePoints[] = "$x,$y";
            $polygonPoints[] = "$x,$y";
        }
        $polygonPoints[] = "570,200";

        $chartLinePoints = implode(' ', $linePoints);
        $chartPolygonPoints = implode(' ', $polygonPoints);

        $recentClients = User::where('role', 'member')
            ->when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })
            ->with(['profile', 'latestMeasurement', 'activeRoutine.routine'])
            ->orderBy('id', 'desc')
            ->take(3)
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
            'chartLinePoints',
            'chartPolygonPoints',
            'recentClients'
        ));
    }

    /**
     * Mis Clientes directory.
     */
    public function clientes()
    {
        $gymId = $this->getActiveGymId();
        
        $query = User::when($gymId !== 'all', function($q) use ($gymId) { $q->where('gym_id', $gymId); })
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
            ->when($gymId !== 'all', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->with([
                'profile', 
                'bodyMeasurements' => function($q) {
                    $q->orderBy('measured_at', 'asc');
                }, 
                'latestMeasurement', 
                'activeRoutine.routine', 
                'activeRoutine.assigner', 
                'activeMealPlan.mealPlan', 
                'activeMealPlan.assigner', 
                'activeMembership.plan'
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
            
            $xStep = $measurements->count() > 1 ? (540 / ($measurements->count() - 1)) : 540;
            $pts = [];
            $polyPts = ["30,200"];
            
            foreach ($measurements as $index => $m) {
                $x = 30 + ($index * $xStep);
                $y = 180 - ((($m->weight_kg - $minWeight) / $weightRange) * 140);
                $pts[] = "$x,$y";
                $polyPts[] = "$x,$y";
                $weightDates[] = Carbon::parse($m->measured_at)->format('d/m');
                $weightValues[] = $m->weight_kg;
            }
            $polyPts[] = "570,200";
            
            $weightPoints = implode(' ', $pts);
            $weightPolygonPoints = implode(' ', $polyPts);
        }

        // Fetch routines & meal plans for assignment modals scoped to this gym
        $routines = WorkoutRoutine::where('gym_id', $cliente->gym_id)->where('is_active', 1)->get();
        $mealPlans = MealPlan::where('gym_id', $cliente->gym_id)->where('is_active', 1)->get();

        return view('clientes.show', compact(
            'cliente',
            'weightPoints',
            'weightPolygonPoints',
            'weightDates',
            'weightValues',
            'routines',
            'mealPlans'
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
     * Store new client.
     */
    public function storeCliente(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
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
        ]);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        // Deactivate existing assignments
        UserAssignedRoutine::where('user_id', $id)->update(['is_active' => 0]);

        // Create assignment
        UserAssignedRoutine::create([
            'user_id' => $id,
            'routine_id' => $request->routine_id,
            'assigned_by' => $trainerId,
            'start_date' => $request->start_date,
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
            ->withCount(['assignments as active_assignments_count' => function($q) {
                $q->where('is_active', 1);
            }])->get();

        $totalClients = User::where('role', 'member')->where('gym_id', $gymId)->count();
        
        $activeAssignmentsCount = UserAssignedRoutine::where('is_active', 1)
            ->whereHas('user', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })->count();

        $popularRoutine = WorkoutRoutine::where('gym_id', $gymId)
            ->withCount(['assignments' => function($q) {
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
     * Planes de Nutrición.
     */
    public function nutricion()
    {
        $gymId = $this->getActiveGymId();

        $dietas = MealPlan::where('gym_id', $gymId)
            ->withCount(['assignments as active_assignments_count' => function($q) {
                $q->where('is_active', 1);
            }])->get();

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
            ->with(['days.breakfast', 'days.snack1', 'days.lunch', 'days.snack2', 'days.dinner'])
            ->findOrFail($id);

        return view('nutricion.comidas', compact('plan'));
    }

    /**
     * Edit exercises inside a workout routine template.
     */
    public function editEjercicios($id)
    {
        $gymId = $this->getActiveGymId();

        $routine = WorkoutRoutine::where('gym_id', $gymId)
            ->with('days.exercises.exercise')
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
                ->with('days.exercises.exercise')
                ->findOrFail($id);
        }

        $exercises = Exercise::where('gym_id', $gymId)->orderBy('name')->get();

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

        RoutineExercise::create([
            'routine_day_id' => $day->id,
            'exercise_id' => $request->exercise_id,
            'sets' => $request->sets,
            'reps' => $request->reps,
            'rest_seconds' => $request->rest_seconds ?? 60,
            'order_index' => $maxOrder + 1,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Ejercicio añadido exitosamente.');
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

        return redirect()->back()->with('success', 'Ejercicio actualizado exitosamente.');
    }

    /**
     * Remove an exercise from a day.
     */
    public function removeEjercicio($id, $routine_exercise_id)
    {
        $ex = RoutineExercise::findOrFail($routine_exercise_id);
        $ex->delete();

        return redirect()->back()->with('success', 'Ejercicio removido del día.');
    }

    /**
     * Assign routine from routines list to a user.
     */
    public function assignRoutineToUser(Request $request, $routine_id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
        ]);

        $trainer = Trainer::where('user_id', auth()->user()->id)->first();
        $trainerId = $trainer ? $trainer->id : null;

        UserAssignedRoutine::where('user_id', $request->user_id)->update(['is_active' => 0]);

        UserAssignedRoutine::create([
            'user_id' => $request->user_id,
            'routine_id' => $routine_id,
            'assigned_by' => $trainerId,
            'start_date' => $request->start_date,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Rutina asignada exitosamente.');
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

        return redirect()->back()->with('success', 'Plan de nutrición asignado exitosamente.');
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
            ->when($gymId !== 'all', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function($q) use ($queryStr) {
                $q->where('email', 'like', "%{$queryStr}%")
                  ->orWhereHas('profile', function($pq) use ($queryStr) {
                      $pq->where('first_name', 'like', "%{$queryStr}%")
                        ->orWhere('last_name', 'like', "%{$queryStr}%")
                        ->orWhere('phone', 'like', "%{$queryStr}%");
                  });
            })
            ->with(['profile', 'gym'])
            ->take(20)
            ->get();

        // Search Workout Routines
        $rutinas = WorkoutRoutine::when($gymId !== 'all', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function($q) use ($queryStr) {
                $q->where('name', 'like', "%{$queryStr}%")
                  ->orWhere('description', 'like', "%{$queryStr}%")
                  ->orWhere('goal_type', 'like', "%{$queryStr}%");
            })
            ->with('gym')
            ->take(20)
            ->get();

        // Search Meal Plans (Dietas)
        $dietas = MealPlan::when($gymId !== 'all', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function($q) use ($queryStr) {
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
            ->when($gymId !== 'all', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->where(function($q) use ($queryStr) {
                $q->where('email', 'like', "%{$queryStr}%")
                  ->orWhereHas('profile', function($pq) use ($queryStr) {
                      $pq->where('first_name', 'like', "%{$queryStr}%")
                        ->orWhere('last_name', 'like', "%{$queryStr}%");
                  });
            })
            ->with(['profile', 'gym'])
            ->take(5)
            ->get();

        $results = $users->map(function($user) {
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
}
