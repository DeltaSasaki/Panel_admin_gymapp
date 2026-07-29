<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\SaasSubscriptionPlan;
use App\Models\Gym;

class SaasAndGymSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. SaaS Subscription Plans
        $planBasic = SaasSubscriptionPlan::create([
            'id' => 1,
            'name' => 'Plan Básico',
            'description' => 'Ideal para pequeños boxes y entrenamientos personales. Límite de 50 socios y 3 entrenadores.',
            'monthly_price' => 29.99,
            'currency' => 'USD',
            'max_users' => 50,
            'max_trainers' => 3,
            'is_active' => 1,
            'createdAt' => $now,
        ]);

        $planPro = SaasSubscriptionPlan::create([
            'id' => 2,
            'name' => 'Plan Pro',
            'description' => 'Para gimnasios en crecimiento. Límite de 200 socios y 10 entrenadores.',
            'monthly_price' => 59.99,
            'currency' => 'USD',
            'max_users' => 200,
            'max_trainers' => 10,
            'is_active' => 1,
            'createdAt' => $now,
        ]);

        $planEnterprise = SaasSubscriptionPlan::create([
            'id' => 3,
            'name' => 'Plan Premium Enterprise',
            'description' => 'Acceso total sin límites para grandes sucursales o cadenas.',
            'monthly_price' => 129.99,
            'currency' => 'USD',
            'max_users' => null, // Sin límite
            'max_trainers' => null, // Sin límite
            'is_active' => 1,
            'createdAt' => $now,
        ]);

        // 2. SaaS Modules & Module Assignments
        if (Schema::hasTable('saas_modules')) {
            $modules = [
                ['id' => 1, 'code_name' => 'store', 'display_name' => 'Tienda Virtual & POS', 'description' => 'Módulo de ventas de productos e inventario.'],
                ['id' => 2, 'code_name' => 'nutrition', 'display_name' => 'Planes Nutricionales', 'description' => 'Creación y seguimiento de dietas y alimentos.'],
                ['id' => 3, 'code_name' => 'gamification', 'display_name' => 'Gamificación & Logros', 'description' => 'Sistema de XP, tokens, niveles y desafíos.'],
                ['id' => 4, 'code_name' => 'classes', 'display_name' => 'Gestión de Clases', 'description' => 'Horarios y reservas de clases grupales.'],
                ['id' => 5, 'code_name' => 'assessments', 'display_name' => 'Evaluaciones Físicas', 'description' => 'Fichas antropométricas y valoración corporal.'],
                ['id' => 6, 'code_name' => 'access_control', 'display_name' => 'Control de Acceso', 'description' => 'Registro de torniquetes y asistencias.'],
            ];

            foreach ($modules as $m) {
                DB::table('saas_modules')->insert([
                    'id' => $m['id'],
                    'code_name' => $m['code_name'],
                    'display_name' => $m['display_name'],
                    'description' => $m['description'],
                ]);
            }

            if (Schema::hasTable('saas_plan_modules')) {
                foreach ($modules as $m) {
                    DB::table('saas_plan_modules')->insert([
                        'plan_id' => $planEnterprise->id,
                        'module_id' => $m['id'],
                    ]);
                    DB::table('saas_plan_modules')->insert([
                        'plan_id' => $planPro->id,
                        'module_id' => $m['id'],
                    ]);
                    if (in_array($m['id'], [4, 5, 6])) {
                        DB::table('saas_plan_modules')->insert([
                            'plan_id' => $planBasic->id,
                            'module_id' => $m['id'],
                        ]);
                    }
                }
            }
        }

        // 3. Gyms (5 Gyms, all on Enterprise plan for seed testing)
        $gym1 = Gym::create([
            'id' => 1,
            'name' => 'GymFlow HQ Central',
            'slug' => 'gymflow-hq',
            'current_plan_id' => $planEnterprise->id,
            'subscription_status' => 'active',
            'address' => 'Av. de los Deportes 450, Madrid',
            'phone' => '+34 912 345 678',
            'email' => 'info@gymflowhq.com',
            'logo_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=200',
            'primary_color' => '#10B981',
            'secondary_color' => '#065F46',
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
            'createdAt' => $now->copy()->subMonths(12),
            'updatedAt' => $now,
        ]);

        $gym2 = Gym::create([
            'id' => 2,
            'name' => 'PowerHouse Studio',
            'slug' => 'powerhouse-studio',
            'current_plan_id' => $planEnterprise->id,
            'subscription_status' => 'active',
            'address' => 'Calle Gran Vía 88, Barcelona',
            'phone' => '+34 931 999 888',
            'email' => 'contact@powerhousestudio.com',
            'logo_url' => 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=200',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E3A8A',
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
            'createdAt' => $now->copy()->subMonths(10),
            'updatedAt' => $now,
        ]);

        $gym3 = Gym::create([
            'id' => 3,
            'name' => 'Titan Fitness Club',
            'slug' => 'titan-fitness',
            'current_plan_id' => $planEnterprise->id,
            'subscription_status' => 'active',
            'address' => 'Av. Libertador 1020, Buenos Aires',
            'phone' => '+54 11 4555 7777',
            'email' => 'admin@titanfitness.com',
            'logo_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=200',
            'primary_color' => '#EF4444',
            'secondary_color' => '#991B1B',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'is_active' => 1,
            'createdAt' => $now->copy()->subMonths(8),
            'updatedAt' => $now,
        ]);

        $gym4 = Gym::create([
            'id' => 4,
            'name' => 'Iron Gym Cross Training',
            'slug' => 'iron-gym',
            'current_plan_id' => $planEnterprise->id,
            'subscription_status' => 'active',
            'address' => 'Carrera 7 #45-12, Bogotá',
            'phone' => '+57 1 610 2030',
            'email' => 'hola@irongym.co',
            'logo_url' => null,
            'primary_color' => '#F59E0B',
            'secondary_color' => '#78350F',
            'timezone' => 'America/Bogota',
            'is_active' => 1,
            'createdAt' => $now->copy()->subMonths(6),
            'updatedAt' => $now,
        ]);

        $gym5 = Gym::create([
            'id' => 5,
            'name' => 'Olympus Performance',
            'slug' => 'olympus-performance',
            'current_plan_id' => $planEnterprise->id,
            'subscription_status' => 'active',
            'address' => 'Av. Insurgentes Sur 500, CDMX',
            'phone' => '+52 55 5234 5678',
            'email' => 'contacto@olympusfit.mx',
            'logo_url' => null,
            'primary_color' => '#8B5CF6',
            'secondary_color' => '#4C1D95',
            'timezone' => 'America/Mexico_City',
            'is_active' => 1,
            'createdAt' => $now->copy()->subMonths(4),
            'updatedAt' => $now,
        ]);

        // 4. Gym SaaS Subscriptions History
        if (Schema::hasTable('gym_subscriptions')) {
            $gyms = [$gym1, $gym2, $gym3, $gym4, $gym5];
            foreach ($gyms as $gym) {
                DB::table('gym_subscriptions')->insert([
                    'gym_id' => $gym->id,
                    'plan_id' => $gym->current_plan_id,
                    'start_date' => $now->copy()->subMonths(6)->toDateString(),
                    'end_date' => $now->copy()->addMonths(6)->toDateString(),
                    'status' => 'active',
                    'payment_method' => 'Tarjeta de Crédito / Stripe',
                    'reference_code' => 'SUB-REF-' . strtoupper(substr(md5($gym->id . time()), 0, 8)),
                    'createdAt' => $now->copy()->subMonths(6),
                ]);
            }
        }

        // 5. Gym Promotions
        if (Schema::hasTable('gym_promotions')) {
            DB::table('gym_promotions')->insert([
                'gym_id' => $gym1->id,
                'title' => 'Super Verano 3x2',
                'description' => 'Paga 2 meses y entrena 3 meses completos con pase VIP.',
                'months_count' => 3,
                'discount_pct' => 33.33,
                'promotional_price' => 80.00,
                'valid_until' => $now->copy()->addDays(30)->toDateString(),
                'is_active' => 1,
            ]);

            DB::table('gym_promotions')->insert([
                'gym_id' => $gym2->id,
                'title' => 'Plan Pareja',
                'description' => 'Descuento del 20% si te inscribes con un amigo o pareja.',
                'months_count' => 1,
                'discount_pct' => 20.00,
                'promotional_price' => 35.00,
                'valid_until' => $now->copy()->addDays(45)->toDateString(),
                'is_active' => 1,
            ]);
        }
    }
}
