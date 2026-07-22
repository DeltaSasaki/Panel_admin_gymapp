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
     * List all gym sucursales.
     */
    public function index()
    {
        $this->checkSuperadmin();
        
        $gyms = Gym::with('plan')->withCount([
            'users as members_count' => function($q) {
                $q->where('role', 'member');
            },
            'users as staff_count' => function($q) {
                $q->where('role', 'trainer');
            }
        ])->orderBy('name')->get();

        $plans = SaasSubscriptionPlan::where('is_active', 1)->orderBy('name')->get();

        return view('superadmin.gyms.index', compact('gyms', 'plans'));
    }

    /**
     * Create a new gym sucursal.
     */
    public function store(Request $request)
    {
        $this->checkSuperadmin();
        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:50|unique:gyms,slug',
            'current_plan_id' => 'nullable|exists:saas_subscription_plans,id',
            'subscription_status' => 'nullable|in:active,past_due,canceled,trialing',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:80',
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logos'), $filename);
            $logoUrl = 'uploads/logos/' . $filename;
        }

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

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
            'timezone' => $request->timezone ?? 'Europe/Madrid',
            'is_active' => 1,
        ]);

        AdminAuditLog::logAction('INSERT', 'gyms', $gym->id, null, $gym->toArray(), $gym->id);

        // Notify other Super Admins
        $otrosSuperAdmins = User::where('role', 'superadmin')
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($otrosSuperAdmins as $sa) {
            Notification::create([
                'user_id' => $sa->id,
                'title' => 'Nueva sucursal registrada',
                'body' => 'El superadmin ' . (auth()->user()->profile->first_name ?? 'Soporte') . ' ha registrado la sucursal: ' . $gym->name,
                'type' => 'general',
                'is_read' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Sucursal de gimnasio creada exitosamente.');
    }

    /**
     * Update an existing gym sucursal.
     */
    public function update(Request $request, $id)
    {
        $this->checkSuperadmin();
        $gym = Gym::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:50|unique:gyms,slug,' . $id,
            'current_plan_id' => 'nullable|exists:saas_subscription_plans,id',
            'subscription_status' => 'nullable|in:active,past_due,canceled,trialing',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:80',
        ]);

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
            'timezone' => $request->timezone ?? 'Europe/Madrid',
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo file if it exists and is a local file
            if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
                @unlink(public_path($gym->logo_url));
            }

            $file = $request->file('logo');
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logos'), $filename);
            $data['logo_url'] = 'uploads/logos/' . $filename;
        } elseif ($request->remove_logo == '1') {
            // Delete current logo file and clear url
            if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
                @unlink(public_path($gym->logo_url));
            }
            $data['logo_url'] = null;
        }

        $gym->update($data);
        AdminAuditLog::logAction('UPDATE', 'gyms', $gym->id, $oldGym, $gym->fresh()->toArray(), $gym->id);

        return redirect()->back()->with('success', 'Sucursal de gimnasio actualizada exitosamente.');
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

        return redirect()->back()->with('success', 'Estado de la sucursal actualizado.');
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
            return redirect()->back()->withErrors(['gym' => 'No puedes eliminar la sucursal actual de tu sesión.']);
        }

        $oldGym = $gym->toArray();

        // Delete old logo file if it exists and is a local file
        if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
            @unlink(public_path($gym->logo_url));
        }

        $gym->delete();
        AdminAuditLog::logAction('DELETE', 'gyms', $gym->id, $oldGym, null, $gym->id);

        return redirect()->back()->with('success', 'Sucursal eliminada exitosamente.');
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

        return redirect()->back()->with('success', 'Plan de suscripción SaaS creado exitosamente.');
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

        return redirect()->back()->with('success', 'Plan de suscripción SaaS actualizado exitosamente.');
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
        return redirect()->back()->with('success', "El plan '{$plan->name}' ha sido {$statusLabel} exitosamente.");
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
