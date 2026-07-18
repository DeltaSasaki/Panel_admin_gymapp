<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gym;
use App\Models\User;
use App\Models\SaasSubscriptionPlan;
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

        Gym::create([
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

        return redirect()->back()->with('success', 'Sucursal de gimnasio actualizada exitosamente.');
    }

    /**
     * Toggle sucursal active status.
     */
    public function toggleStatus($id)
    {
        $this->checkSuperadmin();
        $gym = Gym::findOrFail($id);
        
        $newStatus = $gym->is_active ? 0 : 1;
        $gym->update(['is_active' => $newStatus]);

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

        // Delete old logo file if it exists and is a local file
        if ($gym->logo_url && file_exists(public_path($gym->logo_url))) {
            @unlink(public_path($gym->logo_url));
        }

        $gym->delete();

        return redirect()->back()->with('success', 'Sucursal eliminada exitosamente.');
    }
}
