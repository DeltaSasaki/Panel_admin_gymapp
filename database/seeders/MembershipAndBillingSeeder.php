<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPayment;
use App\Models\PromoCode;
use App\Models\User;

class MembershipAndBillingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Membership Plans per Gym
        $plansData = [
            ['name' => 'Pase Diario', 'days' => 1, 'price' => 10.00, 'trainer' => 0],
            ['name' => 'Plan Mensual Estándar', 'days' => 30, 'price' => 45.00, 'trainer' => 0],
            ['name' => 'Plan Mensual VIP + Entrenador', 'days' => 30, 'price' => 85.00, 'trainer' => 1],
            ['name' => 'Plan Trimestral Fit', 'days' => 90, 'price' => 120.00, 'trainer' => 0],
            ['name' => 'Plan Anual Elite', 'days' => 365, 'price' => 420.00, 'trainer' => 1],
        ];

        $gymPlans = [];
        for ($gymId = 1; $gymId <= 5; $gymId++) {
            foreach ($plansData as $p) {
                $plan = MembershipPlan::create([
                    'gym_id' => $gymId,
                    'name' => $p['name'],
                    'description' => "Acceso a las instalaciones del gimnasio por {$p['days']} días.",
                    'duration_days' => $p['days'],
                    'price' => $p['price'],
                    'currency' => 'USD',
                    'includes_trainer' => $p['trainer'],
                    'is_active' => 1,
                    'createdAt' => $now->copy()->subMonths(12),
                    'updatedAt' => $now,
                ]);
                $gymPlans[$gymId][] = $plan;
            }
        }

        // 2. Promo Codes
        $promos = [];
        for ($gymId = 1; $gymId <= 5; $gymId++) {
            $promos[$gymId][] = PromoCode::create([
                'gym_id' => $gymId,
                'code' => "WELCOMEG{$gymId}",
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'valid_from' => $now->copy()->subMonths(6),
                'valid_until' => $now->copy()->addMonths(6),
                'max_uses' => 500,
                'current_uses' => 0,
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(6),
            ]);
            $promos[$gymId][] = PromoCode::create([
                'gym_id' => $gymId,
                'code' => "VERANOG{$gymId}",
                'discount_type' => 'fixed',
                'discount_value' => 10.00,
                'valid_from' => $now->copy()->subMonths(3),
                'valid_until' => $now->copy()->addMonths(3),
                'max_uses' => 200,
                'current_uses' => 0,
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(3),
            ]);
        }

        // 3. User Memberships and Payments for all members
        $members = User::where('role', 'member')->get();

        $paymentMethods = ['cash', 'card', 'transfer', 'other'];

        foreach ($members as $index => $member) {
            $gymId = $member->gym_id;
            $availablePlans = $gymPlans[$gymId];
            
            // Choose plan (mostly monthly/quarterly/annual)
            $selectedPlan = $availablePlans[$index % count($availablePlans)];

            // Ensure start date is in the past so membership is active NOW and during past attendance logs
            $startDate = $now->copy()->subDays(60);
            $endDate = $startDate->copy()->addDays(max($selectedPlan->duration_days, 180)); // 180 days so it stays active

            $membership = UserMembership::create([
                'user_id' => $member->id,
                'gym_id' => $gymId,
                'plan_id' => $selectedPlan->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => 'active',
                'payment_status' => 'paid',
                'notes' => 'Membresía activa generada por el sistema.',
                'createdAt' => $startDate,
                'updatedAt' => $now,
            ]);

            // Create 1-2 Membership Payments for each membership
            $promo = ($index % 3 == 0) ? $promos[$gymId][0] : null;
            $amount = $selectedPlan->price;
            if ($promo) {
                if ($promo->discount_type === 'percentage') {
                    $amount = round($amount * (1 - ($promo->discount_value / 100)), 2);
                } else {
                    $amount = max(0, $amount - $promo->discount_value);
                }
            }

            // Get an admin ID for received_by
            $admin = User::where('gym_id', $gymId)->where('role', 'admin')->first();
            $adminId = $admin ? $admin->id : $member->id;

            MembershipPayment::create([
                'membership_id' => $membership->id,
                'user_id' => $member->id,
                'promo_code_id' => $promo ? $promo->id : null,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_method' => $paymentMethods[$index % count($paymentMethods)],
                'payment_date' => $startDate->toDateTimeString(),
                'reference_code' => 'PAY-' . strtoupper(substr(md5($membership->id . $index), 0, 10)),
                'received_by' => $adminId,
                'receipt_url' => 'https://gymflow.app/receipts/PAY-' . sprintf('%06d', $membership->id) . '.pdf',
                'notes' => 'Pago de inscripción procesado correctamente.',
                'createdAt' => $startDate,
                'updatedAt' => $startDate,
            ]);

            // Add an expired previous membership for 40% of members for historical data
            if ($index % 5 < 2) {
                $pastStart = $startDate->copy()->subDays(120);
                $pastEnd = $pastStart->copy()->addDays(30);

                $pastMembership = UserMembership::create([
                    'user_id' => $member->id,
                    'gym_id' => $gymId,
                    'plan_id' => $selectedPlan->id,
                    'start_date' => $pastStart->toDateString(),
                    'end_date' => $pastEnd->toDateString(),
                    'status' => 'expired',
                    'payment_status' => 'paid',
                    'notes' => 'Membresía del periodo anterior ya completada.',
                    'createdAt' => $pastStart,
                    'updatedAt' => $pastEnd,
                ]);

                MembershipPayment::create([
                    'membership_id' => $pastMembership->id,
                    'user_id' => $member->id,
                    'promo_code_id' => null,
                    'amount' => $selectedPlan->price,
                    'currency' => 'USD',
                    'payment_method' => 'card',
                    'payment_date' => $pastStart->toDateTimeString(),
                    'reference_code' => 'PAY-HIST-' . sprintf('%06d', $pastMembership->id),
                    'received_by' => $adminId,
                    'receipt_url' => null,
                    'notes' => 'Pago histórico completado.',
                    'createdAt' => $pastStart,
                    'updatedAt' => $pastStart,
                ]);
            }
        }
    }
}
