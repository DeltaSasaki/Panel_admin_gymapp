<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gym;
use App\Models\User;
use App\Models\SaasSubscriptionPlan;
use App\Models\AdminAuditLog;
use App\Models\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GymController extends Controller
{
    /**
     * Helper to verify superadmin status.
     */
    private function checkSuperadmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Acceso Denegado. Solo superadministradores pueden gestionar sucursales.');
        }
    }

    /**
     * Get predefined grouped list of timezones.
     */
    public static function getTimezonesList()
    {
        return [
            'América del Sur' => [
                'America/Caracas' => 'Venezuela (Caracas) - GMT-4',
                'America/Bogota' => 'Colombia (Bogotá) - GMT-5',
                'America/Lima' => 'Perú (Lima) - GMT-5',
                'America/Santiago' => 'Chile (Santiago) - GMT-4 / GMT-3',
                'America/Buenos_Aires' => 'Argentina (Buenos Aires) - GMT-3',
                'America/Montevideo' => 'Uruguay (Montevideo) - GMT-3',
                'America/Asuncion' => 'Paraguay (Asunción) - GMT-4',
                'America/La_Paz' => 'Bolivia (La Paz) - GMT-4',
                'America/Guayaquil' => 'Ecuador (Guayaquil/Quito) - GMT-5',
                'America/Sao_Paulo' => 'Brasil (São Paulo) - GMT-3',
            ],
            'Centroamérica y Caribe' => [
                'America/Mexico_City' => 'México (Ciudad de México) - GMT-6',
                'America/Monterrey' => 'México (Monterrey) - GMT-6',
                'America/Tijuana' => 'México (Tijuana) - GMT-8 / GMT-7',
                'America/Panama' => 'Panamá (Ciudad de Panamá) - GMT-5',
                'America/Santo_Domingo' => 'Rep. Dominicana (Santo Domingo) - GMT-4',
                'America/San_Juan' => 'Puerto Rico (San Juan) - GMT-4',
                'America/Costa_Rica' => 'Costa Rica (San José) - GMT-6',
                'America/Guatemala' => 'Guatemala (Ciudad de Guatemala) - GMT-6',
                'America/Tegucigalpa' => 'Honduras (Tegucigalpa) - GMT-6',
                'America/El_Salvador' => 'El Salvador (San Salvador) - GMT-6',
                'America/Managua' => 'Nicaragua (Managua) - GMT-6',
                'America/Havana' => 'Cuba (La Habana) - GMT-5',
            ],
            'Norteamérica' => [
                'America/New_York' => 'Estados Unidos (Eastern / Miami / NY) - GMT-5 / GMT-4',
                'America/Chicago' => 'Estados Unidos (Central / Chicago / Houston) - GMT-6 / GMT-5',
                'America/Denver' => 'Estados Unidos (Mountain / Denver) - GMT-7 / GMT-6',
                'America/Los_Angeles' => 'Estados Unidos (Pacific / LA / California) - GMT-8 / GMT-7',
                'America/Toronto' => 'Canadá (Toronto) - GMT-5',
                'America/Vancouver' => 'Canadá (Vancouver) - GMT-8',
            ],
            'Europa' => [
                'Europe/Madrid' => 'España (Madrid / Barcelona) - GMT+1 / GMT+2',
                'Europe/Lisbon' => 'Portugal (Lisboa) - GMT+0 / GMT+1',
                'Europe/London' => 'Reino Unido (Londres) - GMT+0 / GMT+1',
                'Europe/Rome' => 'Italia (Roma) - GMT+1',
                'Europe/Paris' => 'Francia (París) - GMT+1',
                'Europe/Berlin' => 'Alemania (Berlín) - GMT+1',
            ],
            'Estándar' => [
                'UTC' => 'UTC (Universal Coordinated Time)',
            ]
        ];
    }

    /**
     * List all gym sucursales.
     */
    public function index()
    {
        $this->checkSuperadmin();
        
        $gyms = Gym::with(['plan', 'admin.profile'])->withCount([
            'users as members_count' => function($q) {
                $q->where('role', 'member');
            },
            'users as staff_count' => function($q) {
                $q->whereIn('role', ['trainer', 'cajero']);
            }
        ])->orderBy('name')->get();

        $plans = SaasSubscriptionPlan::where('is_active', 1)->orderBy('name')->get();
        $timezones = self::getTimezonesList();

        return view('superadmin.gyms.index', compact('gyms', 'plans', 'timezones'));
    }

    /**
     * Create a new gym sucursal along with its owner admin account.
     */
    public function store(Request $request)
    {
        $this->checkSuperadmin();
        if ($request->has('current_plan_id') && $request->input('current_plan_id') === '') {
            $request->merge(['current_plan_id' => null]);
        }

        $request->validate([
            // Gym Data
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:50|unique:gyms,slug',
            'current_plan_id' => 'nullable|exists:saas_subscription_plans,id',
            'subscription_status' => 'nullable|in:active,past_due,canceled,trialing',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'required|string|max:80',

            // Owner Account Data
            'owner_first_name' => 'required|string|max:80',
            'owner_last_name' => 'required|string|max:80',
            'owner_email' => 'required|email|max:150|unique:users,email',
            'owner_password' => 'required|string|min:6',
            'owner_dni' => 'nullable|string|max:30',
            'owner_phone' => 'nullable|string|max:30',
        ]);

        \DB::beginTransaction();
        try {
            $logoUrl = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/logos'), $filename);
                $logoUrl = 'uploads/logos/' . $filename;
            }

            $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
            $baseSlug = $slug;
            $counter = 1;
            while (Gym::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $gym = Gym::create([
                'name' => $request->name,
                'slug' => $slug,
                'current_plan_id' => $request->current_plan_id,
                'subscription_status' => $request->subscription_status ?? 'trialing',
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'logo_url' => $logoUrl,
                'primary_color' => $request->primary_color ?? '#000000',
                'secondary_color' => $request->secondary_color ?? '#FFFFFF',
                'timezone' => $request->timezone ?? 'America/Caracas',
                'is_active' => 1,
            ]);

            if ($request->current_plan_id && \Illuminate\Support\Facades\Schema::hasTable('gym_subscriptions')) {
                \DB::table('gym_subscriptions')->updateOrInsert(
                    ['gym_id' => $gym->id],
                    [
                        'plan_id' => $request->current_plan_id,
                        'status' => $request->subscription_status ?? 'active',
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addYear()->toDateString(),
                    ]
                );
            }

            // Create Owner Admin User
            $ownerUser = User::create([
                'gym_id' => $gym->id,
                'email' => strtolower(trim($request->owner_email)),
                'password_hash' => \Illuminate\Support\Facades\Hash::make($request->owner_password),
                'role' => 'admin',
                'is_active' => 1,
                'email_verified' => 1,
            ]);

            // Create Owner Profile
            \App\Models\UserProfile::create([
                'user_id' => $ownerUser->id,
                'first_name' => trim($request->owner_first_name),
                'last_name' => trim($request->owner_last_name),
                'dni' => $request->owner_dni ? trim($request->owner_dni) : null,
                'phone' => $request->owner_phone ? trim($request->owner_phone) : null,
            ]);

            \DB::commit();

            AdminAuditLog::logAction('INSERT', 'gyms', $gym->id, null, $gym->toArray(), $gym->id);
            AdminAuditLog::logAction('INSERT', 'users', $ownerUser->id, null, ['email' => $ownerUser->email, 'role' => 'admin', 'gym_id' => $gym->id], $gym->id);

            // Notify other Super Admins
            $otrosSuperAdmins = User::where('role', 'superadmin')
                ->where('id', '!=', auth()->id())
                ->get();

            foreach ($otrosSuperAdmins as $sa) {
                Notification::create([
                    'user_id' => $sa->id,
                    'title' => 'Nueva sucursal y dueño registrados',
                    'body' => 'El superadmin ' . (auth()->user()->profile->first_name ?? 'Soporte') . ' ha registrado la sucursal: ' . $gym->name . ' con dueño ' . $ownerUser->email,
                    'type' => 'general',
                    'is_read' => 0,
                ]);
            }

            $message = "Sucursal '{$gym->name}' y cuenta de dueño creadas exitosamente.";

            if ($request->ajax() || $request->wantsJson()) {
                $gym->load(['plan', 'admin.profile']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'gym' => $gym
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            \DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar sucursal: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Error al registrar sucursal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update an existing gym sucursal and optionally its owner account.
     */
    public function update(Request $request, $id)
    {
        $this->checkSuperadmin();
        $gym = Gym::with('admin.profile')->findOrFail($id);

        if ($request->has('current_plan_id') && $request->input('current_plan_id') === '') {
            $request->merge(['current_plan_id' => null]);
        }

        $ownerUserId = $gym->admin ? $gym->admin->id : null;

        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:50|unique:gyms,slug,' . $id,
            'current_plan_id' => 'nullable|exists:saas_subscription_plans,id',
            'subscription_status' => 'nullable|in:active,past_due,canceled,trialing',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'required|string|max:80',

            // Optional Owner updates
            'owner_first_name' => 'nullable|string|max:80',
            'owner_last_name' => 'nullable|string|max:80',
            'owner_email' => 'nullable|email|max:150|unique:users,email,' . ($ownerUserId ?? 'NULL'),
            'owner_password' => 'nullable|string|min:6',
            'owner_dni' => 'nullable|string|max:30',
            'owner_phone' => 'nullable|string|max:30',
        ]);

        \DB::beginTransaction();
        try {
            $oldGym = $gym->toArray();
            $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

            $data = [
                'name' => $request->name,
                'slug' => $slug,
                'current_plan_id' => $request->current_plan_id,
                'subscription_status' => $request->subscription_status ?? 'trialing',
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'primary_color' => $request->primary_color ?? '#000000',
                'secondary_color' => $request->secondary_color ?? '#FFFFFF',
                'timezone' => $request->timezone ?? 'America/Caracas',
            ];

            if ($request->hasFile('logo')) {
                if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
                    @unlink(public_path($gym->logo_url));
                }
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/logos'), $filename);
                $data['logo_url'] = 'uploads/logos/' . $filename;
            } elseif ($request->remove_logo == '1') {
                if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
                    @unlink(public_path($gym->logo_url));
                }
                $data['logo_url'] = null;
            }

            $gym->update($data);

            if (\Illuminate\Support\Facades\Schema::hasTable('gym_subscriptions')) {
                if ($request->current_plan_id) {
                    \DB::table('gym_subscriptions')->updateOrInsert(
                        ['gym_id' => $gym->id],
                        [
                            'plan_id' => $request->current_plan_id,
                            'status' => $request->subscription_status ?? 'active',
                            'start_date' => now()->toDateString(),
                            'end_date' => now()->addYear()->toDateString(),
                        ]
                    );
                }
            }

            // Update or Create Owner Account if fields provided
            if ($request->filled('owner_email') || $request->filled('owner_first_name')) {
                if ($gym->admin) {
                    $owner = $gym->admin;
                    $ownerUpdates = [];
                    if ($request->filled('owner_email')) {
                        $ownerUpdates['email'] = strtolower(trim($request->owner_email));
                    }
                    if ($request->filled('owner_password')) {
                        $ownerUpdates['password_hash'] = \Illuminate\Support\Facades\Hash::make($request->owner_password);
                    }
                    if (!empty($ownerUpdates)) {
                        $owner->update($ownerUpdates);
                    }

                    if ($owner->profile) {
                        $owner->profile->update([
                            'first_name' => $request->owner_first_name ? trim($request->owner_first_name) : $owner->profile->first_name,
                            'last_name' => $request->owner_last_name ? trim($request->owner_last_name) : $owner->profile->last_name,
                            'dni' => $request->has('owner_dni') ? trim($request->owner_dni) : $owner->profile->dni,
                            'phone' => $request->has('owner_phone') ? trim($request->owner_phone) : $owner->profile->phone,
                        ]);
                    } else {
                        \App\Models\UserProfile::create([
                            'user_id' => $owner->id,
                            'first_name' => trim($request->owner_first_name ?? 'Admin'),
                            'last_name' => trim($request->owner_last_name ?? ''),
                            'dni' => $request->owner_dni ? trim($request->owner_dni) : null,
                            'phone' => $request->owner_phone ? trim($request->owner_phone) : null,
                        ]);
                    }
                } elseif ($request->filled('owner_email') && $request->filled('owner_password')) {
                    // Create new owner admin if gym didn't have one
                    $newOwner = User::create([
                        'gym_id' => $gym->id,
                        'email' => strtolower(trim($request->owner_email)),
                        'password_hash' => \Illuminate\Support\Facades\Hash::make($request->owner_password),
                        'role' => 'admin',
                        'is_active' => 1,
                        'email_verified' => 1,
                    ]);

                    \App\Models\UserProfile::create([
                        'user_id' => $newOwner->id,
                        'first_name' => trim($request->owner_first_name ?? 'Admin'),
                        'last_name' => trim($request->owner_last_name ?? ''),
                        'dni' => $request->owner_dni ? trim($request->owner_dni) : null,
                        'phone' => $request->owner_phone ? trim($request->owner_phone) : null,
                    ]);
                }
            }

            \DB::commit();

            AdminAuditLog::logAction('UPDATE', 'gyms', $gym->id, $oldGym, $gym->fresh()->toArray(), $gym->id);

            $message = 'Sucursal de gimnasio actualizada exitosamente.';

            if ($request->ajax() || $request->wantsJson()) {
                $gym->load(['plan', 'admin.profile']);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'gym' => $gym
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            \DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar sucursal: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Error al actualizar sucursal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Toggle sucursal active status.
     */
    public function toggleStatus($id)
    {
        $this->checkSuperadmin();
        $gym = Gym::findOrFail($id);
        
        $oldGym = ['is_active' => $gym->is_active];
        $newStatus = $gym->is_active ? 0 : 1;
        $gym->update(['is_active' => $newStatus]);
        AdminAuditLog::logAction('UPDATE', 'gyms', $gym->id, $oldGym, ['is_active' => $newStatus], $gym->id);

        $message = $newStatus 
            ? "Sucursal '{$gym->name}' reactivada con éxito."
            : "Sucursal '{$gym->name}' suspendida / inhabilitada con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gym_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete a sucursal.
     */
    public function destroy($id)
    {
        $this->checkSuperadmin();
        $gym = Gym::findOrFail($id);
        
        // Prevent deleting the main gym you are currently logged in under
        if ($gym->id == auth()->user()->gym_id) {
            $errorMsg = 'No puedes eliminar la sucursal actual de tu sesión.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withErrors(['gym' => $errorMsg]);
        }

        $oldGym = $gym->toArray();

        // Delete old logo file if it exists and is a local file
        if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
            @unlink(public_path($gym->logo_url));
        }

        $gym->delete();
        AdminAuditLog::logAction('DELETE', 'gyms', $gym->id, $oldGym, null, $gym->id);

        $message = 'Sucursal eliminada exitosamente.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gym_id' => $id
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /* =========================================================================
     *  SaaS Subscription Plans Management (Superadmin Only)
     * ========================================================================= */

    /**
     * List all SaaS Subscription Plans.
     */
    public function plansIndex()
    {
        $this->checkSuperadmin();
        $plans = SaasSubscriptionPlan::withCount('gyms')->orderBy('monthly_price', 'asc')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    /**
     * Store a new SaaS Subscription Plan.
     */
    public function plansStore(Request $request)
    {
        $this->checkSuperadmin();

        $request->validate([
            'name' => 'required|string|max:100|unique:saas_subscription_plans,name',
            'description' => 'nullable|string|max:500',
            'monthly_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'max_users' => 'nullable|integer|min:1',
            'max_trainers' => 'nullable|integer|min:1',
        ]);

        $newPlan = SaasSubscriptionPlan::create([
            'name' => $request->name,
            'description' => $request->description,
            'monthly_price' => $request->monthly_price,
            'currency' => strtoupper($request->currency),
            'max_users' => $request->max_users ?: null,
            'max_trainers' => $request->max_trainers ?: null,
            'is_active' => 1,
        ]);

        AdminAuditLog::logAction('INSERT', 'saas_subscription_plans', $newPlan->id, null, $newPlan->toArray());

        $message = 'Plan de suscripción SaaS creado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            $newPlan->loadCount('gyms');
            return response()->json([
                'success' => true,
                'message' => $message,
                'plan' => $newPlan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an existing SaaS Subscription Plan.
     */
    public function plansUpdate(Request $request, $id)
    {
        $this->checkSuperadmin();
        $plan = SaasSubscriptionPlan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:saas_subscription_plans,name,' . $plan->id,
            'description' => 'nullable|string|max:500',
            'monthly_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'max_users' => 'nullable|integer|min:1',
            'max_trainers' => 'nullable|integer|min:1',
        ]);

        $oldPlan = $plan->toArray();

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'monthly_price' => $request->monthly_price,
            'currency' => strtoupper($request->currency),
            'max_users' => $request->max_users ?: null,
            'max_trainers' => $request->max_trainers ?: null,
        ]);

        AdminAuditLog::logAction('UPDATE', 'saas_subscription_plans', $plan->id, $oldPlan, $plan->fresh()->toArray());

        $message = 'Plan de suscripción SaaS actualizado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            $plan->loadCount('gyms');
            return response()->json([
                'success' => true,
                'message' => $message,
                'plan' => $plan
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle SaaS Subscription Plan active status.
     */
    public function plansToggle($id)
    {
        $this->checkSuperadmin();
        $plan = SaasSubscriptionPlan::findOrFail($id);
        $oldStatus = ['is_active' => $plan->is_active];

        $plan->is_active = $plan->is_active ? 0 : 1;
        $plan->save();

        AdminAuditLog::logAction('UPDATE', 'saas_subscription_plans', $plan->id, $oldStatus, ['is_active' => $plan->is_active]);

        $statusLabel = $plan->is_active ? 'activado' : 'desactivado';
        $message = "El plan '{$plan->name}' ha sido {$statusLabel} exitosamente.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'plan_id' => $id,
                'is_active' => $plan->is_active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Safely delete a SaaS Subscription Plan with dependency check.
     */
    public function plansDestroy($id)
    {
        $this->checkSuperadmin();
        $plan = SaasSubscriptionPlan::findOrFail($id);

        // Safety check: Prevent deleting plans that are currently assigned to any gym
        $gymsCount = Gym::where('current_plan_id', $plan->id)->count();
        if ($gymsCount > 0) {
            return redirect()->back()->withErrors([
                'plan' => "No se puede eliminar el plan '{$plan->name}' porque está asignado actualmente a {$gymsCount} sucursal(es). Puedes deshabilitarlo en su lugar para impedir nuevas asignaciones sin afectar el servicio activo."
            ]);
        }

        $oldPlan = $plan->toArray();
        $plan->delete();
        AdminAuditLog::logAction('DELETE', 'saas_subscription_plans', $plan->id, $oldPlan, null);
        return redirect()->back()->with('success', "Plan de suscripción '{$plan->name}' eliminado exitosamente.");
    }

    /* =========================================================================
     *  Superadmin Audit Logs & Security Trail
     * ========================================================================= */

    /**
     * Display Superadmin Audit Logs.
     */
    public function auditLogsIndex(Request $request)
    {
        $this->checkSuperadmin();

        $query = AdminAuditLog::with(['admin', 'gym']);

        // Filter by Gym
        if ($request->filled('gym_id') && $request->gym_id !== 'all') {
            if ($request->gym_id === 'global') {
                $query->whereNull('gym_id');
            } else {
                $query->where('gym_id', $request->gym_id);
            }
        }

        // Filter by Action Type
        if ($request->filled('action_type') && $request->action_type !== 'all') {
            $query->where('action_type', $request->action_type);
        }

        // Filter by Admin/User ID
        if ($request->filled('admin_id') && $request->admin_id !== 'all') {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by Search Query (Table Name, IP, Record ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('table_name', 'like', "%{$search}%")
                  ->orWhere('record_id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('admin', function($adminQuery) use ($search) {
                      $adminQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('createdAt', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('createdAt', '<=', $request->date_to);
        }

        $logs = $query->orderBy('createdAt', 'desc')->paginate(20)->withQueryString();

        $gyms = Gym::orderBy('name')->get();
        $admins = User::with('profile')->whereIn('role', ['admin', 'superadmin', 'trainer'])->orderBy('email')->get();

        return view('superadmin.auditoria.index', compact('logs', 'gyms', 'admins'));
    }
}
