<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\UserMealPlan;
use App\Models\User;

class NutritionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Recipe Categories
        $categories = [
            ['id' => 1, 'name' => 'Desayunos Proteicos', 'icon' => 'egg'],
            ['id' => 2, 'name' => 'Almuerzos & Cenas Fitness', 'icon' => 'drumstick'],
            ['id' => 3, 'name' => 'Snacks & Batidos', 'icon' => 'cup-straw'],
            ['id' => 4, 'name' => 'Ensaladas & Bowls', 'icon' => 'bowl-food'],
            ['id' => 5, 'name' => 'Postres Saludables', 'icon' => 'cookie'],
        ];

        foreach ($categories as $cat) {
            DB::table('recipe_categories')->insert([
                'id' => $cat['id'],
                'gym_id' => null,
                'name' => $cat['name'],
                'icon_url' => $cat['icon'],
            ]);
        }

        // 2. Ingredients (40 ingredients)
        $ingredientsData = [
            ['Pechuga de Pollo Deshuesada', 'g', 165, 31.0, 0.0, 3.6, 0.0],
            ['Huevos de Gallina Frescos', 'unidad', 155, 13.0, 1.1, 11.0, 0.0],
            ['Arroz Integral Cocido', 'g', 111, 2.6, 23.0, 0.9, 1.8],
            ['Avena en Hojuelas', 'g', 389, 16.9, 66.3, 6.9, 10.6],
            ['Proteína de Suero (Whey Protein)', 'g', 370, 80.0, 6.0, 3.0, 1.0],
            ['Aguacate (Palta)', 'g', 160, 2.0, 8.5, 14.7, 6.7],
            ['Camote / Batata Dulce', 'g', 86, 1.6, 20.1, 0.1, 3.0],
            ['Salmón Fresco', 'g', 208, 20.0, 0.0, 13.0, 0.0],
            ['Espinacas Frescas', 'g', 23, 2.9, 3.6, 0.4, 2.2],
            ['Almendras Naturales', 'g', 579, 21.2, 21.7, 49.9, 12.5],
            ['Leche de Almendras Sin Azúcar', 'ml', 15, 0.5, 0.3, 1.1, 0.2],
            ['Yogur Griego Natural 0%', 'g', 59, 10.0, 3.6, 0.4, 0.0],
            ['Banana / Plátano Ripe', 'g', 89, 1.1, 22.8, 0.3, 2.6],
            ['Aceite de Oliva Extra Virgen', 'ml', 884, 0.0, 0.0, 100.0, 0.0],
            ['Quinoa Cocida', 'g', 120, 4.4, 21.3, 1.9, 2.8],
        ];

        $ingredients = [];
        foreach ($ingredientsData as $idx => $ing) {
            $ingredient = Ingredient::create([
                'id' => $idx + 1,
                'gym_id' => null,
                'is_global' => 1,
                'name' => $ing[0],
                'unit' => $ing[1],
                'calories_per_100g' => $ing[2],
                'protein_g' => $ing[3],
                'carbs_g' => $ing[4],
                'fat_g' => $ing[5],
                'fiber_g' => $ing[6],
                'is_active' => 1,
                'createdAt' => $now->copy()->subYear(),
                'updatedAt' => $now,
            ]);
            $ingredients[] = $ingredient;
        }

        // Expand up to 40 ingredients
        for ($i = count($ingredientsData) + 1; $i <= 40; $i++) {
            $base = $ingredientsData[($i - 1) % count($ingredientsData)];
            $ingredient = Ingredient::create([
                'id' => $i,
                'gym_id' => null,
                'is_global' => 1,
                'name' => $base[0] . " Tipo #" . ($i - count($ingredientsData)),
                'unit' => $base[1],
                'calories_per_100g' => $base[2],
                'protein_g' => $base[3],
                'carbs_g' => $base[4],
                'fat_g' => $base[5],
                'fiber_g' => $base[6],
                'is_active' => 1,
                'createdAt' => $now->copy()->subYear(),
                'updatedAt' => $now,
            ]);
            $ingredients[] = $ingredient;
        }

        // 3. 30 Recipes
        $recipesData = [
            ['cat' => 1, 'name' => 'Omelette de Claras con Espinacas y Palta', 'prep' => 10, 'cal' => 320, 'p' => 28, 'c' => 8, 'f' => 18],
            ['cat' => 1, 'name' => 'Pancakes Proteicos de Avena y Banana', 'prep' => 15, 'cal' => 450, 'p' => 35, 'c' => 55, 'f' => 8],
            ['cat' => 2, 'name' => 'Pechuga a la Plancha con Quinoa y Aguacate', 'prep' => 25, 'cal' => 520, 'p' => 45, 'c' => 40, 'f' => 16],
            ['cat' => 2, 'name' => 'Salmón al Horno con Camote Rosticizado', 'prep' => 30, 'cal' => 580, 'p' => 42, 'c' => 38, 'f' => 22],
            ['cat' => 3, 'name' => 'Batido Anabólico Post-Entreno Whey & Banana', 'prep' => 5, 'cal' => 380, 'p' => 40, 'c' => 45, 'f' => 4],
            ['cat' => 4, 'name' => 'Bowl Fit de Pollo, Arroz Integral y Vegetales', 'prep' => 20, 'cal' => 490, 'p' => 40, 'c' => 50, 'f' => 12],
        ];

        $recipes = [];
        $recId = 1;

        foreach ($recipesData as $r) {
            $recipe = Recipe::create([
                'id' => $recId,
                'gym_id' => null,
                'category_id' => $r['cat'],
                'name' => $r['name'],
                'description' => "Receta balanceada para rendimiento deportivo y control de macros.",
                'instructions' => "Paso 1: Preparar ingredientes. Paso 2: Cocinar a fuego medio durante el tiempo indicado. Paso 3: Servir caliente.",
                'preparation_min' => $r['prep'],
                'goal_type' => 'gain_muscle',
                'bmi_category' => 'all',
                'calories_total' => $r['cal'],
                'protein_g' => $r['p'],
                'carbs_g' => $r['c'],
                'fat_g' => $r['f'],
                'servings' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200',
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(6),
                'updatedAt' => $now,
            ]);

            // Add ingredients to recipe
            for ($k = 1; $k <= 3; $k++) {
                $ing = $ingredients[($recId * 2 + $k) % count($ingredients)];
                DB::table('recipe_ingredients')->insert([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ing->id,
                    'quantity' => 100.00,
                    'unit' => $ing->unit,
                    'notes' => 'Porción recomendada',
                ]);
            }

            $recipes[] = $recipe;
            $recId++;
        }

        // Expand to 30 recipes
        for ($i = count($recipesData) + 1; $i <= 30; $i++) {
            $baseR = $recipesData[($i - 1) % count($recipesData)];
            $recipe = Recipe::create([
                'id' => $i,
                'gym_id' => null,
                'category_id' => $baseR['cat'],
                'name' => $baseR['name'] . " Var. #" . ($i - count($recipesData)),
                'description' => "Variación nutricional para dieta deportiva.",
                'instructions' => "Cocer a fuego lento e incorporar sazón natural al gusto.",
                'preparation_min' => $baseR['prep'],
                'goal_type' => 'general',
                'bmi_category' => 'all',
                'calories_total' => $baseR['cal'],
                'protein_g' => $baseR['p'],
                'carbs_g' => $baseR['c'],
                'fat_g' => $baseR['f'],
                'servings' => 1,
                'image_url' => null,
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(6),
                'updatedAt' => $now,
            ]);
            $recipes[] = $recipe;
        }

        // 4. 15 Meal Plans
        $mealPlans = [];
        $goals = ['lose_weight', 'gain_muscle', 'maintain', 'general'];

        for ($mpId = 1; $mpId <= 15; $mpId++) {
            $gymId = ($mpId % 5) + 1;
            $goal = $goals[$mpId % count($goals)];

            $plan = MealPlan::create([
                'id' => $mpId,
                'gym_id' => $gymId,
                'name' => "Plan Nutricional " . ucfirst(str_replace('_', ' ', $goal)) . " #{$mpId}",
                'description' => "Plan integral con desglose calórico diario de alta precisión.",
                'goal_type' => $goal,
                'bmi_category' => 'all',
                'duration_weeks' => 4,
                'daily_calories' => 2000.00 + ($mpId * 100),
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(4),
                'updatedAt' => $now,
            ]);
            $mealPlans[] = $plan;

            // Meal plan days (7 days per plan)
            for ($dayNum = 1; $dayNum <= 7; $dayNum++) {
                MealPlanDay::create([
                    'meal_plan_id' => $plan->id,
                    'day_number' => $dayNum,
                    'breakfast_recipe_id' => $recipes[($mpId + 1) % count($recipes)]->id,
                    'snack1_recipe_id' => $recipes[($mpId + 2) % count($recipes)]->id,
                    'lunch_recipe_id' => $recipes[($mpId + 3) % count($recipes)]->id,
                    'snack2_recipe_id' => $recipes[($mpId + 4) % count($recipes)]->id,
                    'dinner_recipe_id' => $recipes[($mpId + 5) % count($recipes)]->id,
                ]);
            }
        }

        // 5. User Meal Plans + 1,000+ User Food Logs
        $members = User::where('role', 'member')->get();

        foreach ($members as $m) {
            $userGymPlans = array_filter($mealPlans, fn($p) => $p->gym_id == $m->gym_id);
            if (empty($userGymPlans)) {
                $userGymPlans = $mealPlans;
            }
            $userGymPlans = array_values($userGymPlans);

            $selectedPlan = $userGymPlans[$m->id % count($userGymPlans)];

            UserMealPlan::create([
                'user_id' => $m->id,
                'meal_plan_id' => $selectedPlan->id,
                'assigned_by' => null,
                'start_date' => $now->copy()->subDays(30)->toDateString(),
                'end_date' => $now->copy()->addDays(30)->toDateString(),
                'is_active' => 1,
                'createdAt' => $now->copy()->subDays(30),
                'updatedAt' => $now,
            ]);

            // Create 3-4 food logs per member (~1,000+ logs)
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
            for ($fl = 1; $fl <= 4; $fl++) {
                $logDate = $now->copy()->subDays($fl * 3);
                $recipe = $recipes[($m->id + $fl) % count($recipes)];

                DB::table('user_food_logs')->insert([
                    'user_id' => $m->id,
                    'gym_id' => $m->gym_id,
                    'log_date' => $logDate->toDateString(),
                    'meal_type' => $mealTypes[$fl - 1],
                    'recipe_id' => $recipe->id,
                    'custom_food_name' => null,
                    'calories' => $recipe->calories_total,
                    'protein_g' => $recipe->protein_g,
                    'carbs_g' => $recipe->carbs_g,
                    'fat_g' => $recipe->fat_g,
                    'createdAt' => $logDate,
                ]);
            }
        }
    }
}
