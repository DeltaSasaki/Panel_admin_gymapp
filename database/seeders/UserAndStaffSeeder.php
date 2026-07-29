<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Trainer;
use App\Models\UserTrainerAssignment;

class UserAndStaffSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('password123');
        $hasCreditBalance = Schema::hasColumn('users', 'credit_balance');

        // Helper array builder
        $makeUserData = function ($gymId, $email, $role, $createdAt, $credit = 0.00) use ($passwordHash, $hasCreditBalance, $now) {
            $data = [
                'gym_id' => $gymId,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => $role,
                'is_active' => 1,
                'email_verified' => 1,
                'createdAt' => $createdAt,
                'updatedAt' => $now,
            ];
            if ($hasCreditBalance) {
                $data['credit_balance'] = $credit;
            }
            return $data;
        };

        // 1. Superadmin User
        $superAdmin = User::create($makeUserData(null, 'superadmin@gymapp.com', 'superadmin', $now->copy()->subYears(2)));
        
        UserProfile::create([
            'user_id' => $superAdmin->id,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'dni' => 'SA-00000000',
            'phone' => '+34 600 000 000',
            'birth_date' => '1985-01-01',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200',
            'createdAt' => $now->copy()->subYears(2),
            'updatedAt' => $now,
        ]);

        // 2. Gym Admins for Gyms 1 to 5
        $adminNames = [
            1 => ['first' => 'Alejandro', 'last' => 'García', 'email' => 'admin@gymflow.com'],
            2 => ['first' => 'Beatriz', 'last' => 'Martínez', 'email' => 'admin@powerhouse.com'],
            3 => ['first' => 'Camilo', 'last' => 'Rodríguez', 'email' => 'admin@titan.com'],
            4 => ['first' => 'Daniela', 'last' => 'López', 'email' => 'admin@irongym.com'],
            5 => ['first' => 'Eduardo', 'last' => 'Sánchez', 'email' => 'admin@olympus.com'],
        ];

        foreach ($adminNames as $gymId => $info) {
            $adminUser = User::create($makeUserData($gymId, $info['email'], 'admin', $now->copy()->subMonths(12)));
            
            UserProfile::create([
                'user_id' => $adminUser->id,
                'first_name' => $info['first'],
                'last_name' => $info['last'],
                'dni' => 'ADM-00' . $gymId,
                'phone' => '+34 611 223 30' . $gymId,
                'birth_date' => '1988-06-15',
                'gender' => ($gymId % 2 == 0) ? 'female' : 'male',
                'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200',
                'createdAt' => $now->copy()->subMonths(12),
                'updatedAt' => $now,
            ]);
        }

        // 3. Trainers (4 trainers per gym = 20 trainers total)
        $trainerData = [
            ['first' => 'Carlos', 'last' => 'Ruiz', 'spec' => 'Crossfit & Funcional', 'cert' => 'NSCA-CPT', 'exp' => 7, 'bio' => 'Especialista en hipertrofia y acondicionamiento metabólico.'],
            ['first' => 'María', 'last' => 'Fernández', 'spec' => 'Pilates & Yoga', 'cert' => 'RYT-500', 'exp' => 5, 'bio' => 'Apasionada de la movilidad articular y corrección postural.'],
            ['first' => 'Javier', 'last' => 'Gómez', 'spec' => 'Powerlifting & Fuerza', 'cert' => 'ISSA Powerlifting', 'exp' => 10, 'bio' => 'Entrenador de atletas de alto rendimiento y levantamiento olímpico.'],
            ['first' => 'Laura', 'last' => 'Torres', 'spec' => 'Nutrición & HIIT', 'cert' => 'Precision Nutrition', 'exp' => 6, 'bio' => 'Enfocada en pérdida de grasa corporal y recomposición física.'],
        ];

        $trainerIds = [];
        $trainerCount = 0;

        for ($gymId = 1; $gymId <= 5; $gymId++) {
            foreach ($trainerData as $index => $t) {
                $trainerCount++;
                $email = "trainer{$trainerCount}@gym{$gymId}.com";

                $u = User::create($makeUserData($gymId, $email, 'trainer', $now->copy()->subMonths(10)));

                UserProfile::create([
                    'user_id' => $u->id,
                    'first_name' => $t['first'],
                    'last_name' => $t['last'] . " (G{$gymId})",
                    'dni' => 'TRN-' . sprintf('%04d', $trainerCount),
                    'phone' => '+34 622 ' . sprintf('%03d', $trainerCount) . ' 000',
                    'birth_date' => Carbon::now()->subYears(25 + ($trainerCount % 15))->toDateString(),
                    'gender' => ($index % 2 == 0) ? 'male' : 'female',
                    'profile_photo' => ($index % 2 == 0) 
                        ? 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?q=80&w=200' 
                        : 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200',
                    'createdAt' => $now->copy()->subMonths(10),
                    'updatedAt' => $now,
                ]);

                $tr = Trainer::create([
                    'user_id' => $u->id,
                    'gym_id' => $gymId,
                    'first_name' => $t['first'],
                    'last_name' => $t['last'] . " (G{$gymId})",
                    'email' => $email,
                    'phone' => '+34 622 ' . sprintf('%03d', $trainerCount) . ' 000',
                    'specialty' => $t['spec'],
                    'certification' => $t['cert'],
                    'experience_years' => $t['exp'],
                    'photo_url' => ($index % 2 == 0) 
                        ? 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?q=80&w=200' 
                        : 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200',
                    'bio' => $t['bio'],
                    'is_active' => 1,
                    'max_clients' => 100,
                    'hire_date' => $now->copy()->subMonths(10)->toDateString(),
                    'salary' => 1500.00 + ($trainerCount * 50),
                    'createdAt' => $now->copy()->subMonths(10),
                    'updatedAt' => $now,
                ]);

                $trainerIds[$gymId][] = $tr->id;
            }
        }

        // 4. Create 320 Members/Users (64 per gym)
        $firstNamesMale = ['Juan', 'Pedro', 'Luis', 'Diego', 'Andrés', 'Gabriel', 'Mateo', 'Lucas', 'Santiago', 'Nicolás', 'Felipe', 'Rodrigo', 'Gonzalo', 'Esteban', 'Javier', 'Tomás', 'Ignacio', 'Sebastián', 'Fernando', 'Álvaro'];
        $firstNamesFemale = ['Ana', 'Sofia', 'Lucía', 'Valentina', 'Camila', 'Isabella', 'Martina', 'Emma', 'Valeria', 'Elena', 'Paula', 'Sara', 'Daniela', 'Natalia', 'Victoria', 'Gabriela', 'Andrea', 'Carolina', 'Claudia', 'Patricia'];
        $lastNames = ['Pérez', 'González', 'Rodríguez', 'Sánchez', 'Ramírez', 'Flores', 'Gómez', 'Díaz', 'Morales', 'Álvarez', 'Romero', 'Gutierrez', 'Navarro', 'Torres', 'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Serrano', 'Blanco'];

        $memberCount = 0;
        for ($gymId = 1; $gymId <= 5; $gymId++) {
            for ($i = 1; $i <= 64; $i++) {
                $memberCount++;
                $isMale = ($i % 2 == 1);
                $fn = $isMale ? $firstNamesMale[array_rand($firstNamesMale)] : $firstNamesFemale[array_rand($firstNamesFemale)];
                $ln = $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)];
                $email = "socio{$memberCount}@gym{$gymId}.com";
                $regDate = $now->copy()->subDays(rand(1, 360));
                $credit = rand(0, 10) > 7 ? rand(10, 100) : 0.00;

                $user = User::create($makeUserData($gymId, $email, 'member', $regDate, $credit));

                UserProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'dni' => sprintf('%08d', 10000000 + $memberCount) . 'X',
                    'phone' => '+34 6' . sprintf('%08d', rand(10000000, 99999999)),
                    'birth_date' => $now->copy()->subYears(rand(18, 55))->subDays(rand(1, 360))->toDateString(),
                    'gender' => $isMale ? 'male' : 'female',
                    'profile_photo' => null,
                    'createdAt' => $regDate,
                    'updatedAt' => $regDate,
                ]);

                // Assign trainer to 60% of members
                if ($i % 5 != 0 && isset($trainerIds[$gymId])) {
                    $assignedTrainerId = $trainerIds[$gymId][$i % count($trainerIds[$gymId])];
                    UserTrainerAssignment::create([
                        'user_id' => $user->id,
                        'trainer_id' => $assignedTrainerId,
                        'assigned_at' => $regDate->toDateTimeString(),
                        'end_date' => null,
                        'is_active' => 1,
                        'notes' => 'Asignación automática inicial de plan de acondicionamiento.',
                        'createdAt' => $regDate,
                        'updatedAt' => $regDate,
                    ]);
                }
            }
        }
    }
}
