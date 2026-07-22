<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'meal_plans';

    protected $fillable = [
        'gym_id',
        'name',
        'description',
        'goal_type',
        'bmi_category',
        'duration_weeks',
        'daily_calories',
        'is_active',
    ];

    public function assignments()
    {
        return $this->hasMany(UserMealPlan::class, 'meal_plan_id');
    }

    public function days()
    {
        return $this->hasMany(MealPlanDay::class, 'meal_plan_id')->orderBy('day_number');
    }

    public function activeAssignmentsCount()
    {
        return $this->assignments()->where('is_active', 1)->count();
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    /**
     * Calculate total macros (protein, carbs, fat) and percentage distribution
     * across all meals in all days of this meal plan.
     */
    public function getMacroTotals()
    {
        $protein = 0;
        $carbs = 0;
        $fat = 0;

        foreach ($this->days as $day) {
            foreach (['breakfast', 'snack1', 'lunch', 'snack2', 'dinner'] as $slot) {
                $recipe = $day->$slot;
                if ($recipe) {
                    $protein += (float) ($recipe->protein_g ?? 0);
                    $carbs += (float) ($recipe->carbs_g ?? 0);
                    $fat += (float) ($recipe->fat_g ?? 0);
                }
            }
        }

        $proteinKcal = $protein * 4;
        $carbKcal = $carbs * 4;
        $fatKcal = $fat * 9;
        $totalKcal = $proteinKcal + $carbKcal + $fatKcal;

        if ($totalKcal > 0) {
            $pPct = (int) round(($proteinKcal / $totalKcal) * 100);
            $cPct = (int) round(($carbKcal / $totalKcal) * 100);
            $fPct = (int) round(($fatKcal / $totalKcal) * 100);

            // Ensure percentages sum to 100%
            $sumPct = $pPct + $cPct + $fPct;
            if ($sumPct !== 100 && $sumPct > 0) {
                $fPct = 100 - ($pPct + $cPct);
            }
        } else {
            $pPct = 0;
            $cPct = 0;
            $fPct = 0;
        }

        return [
            'protein' => (int) round($protein),
            'carbs'   => (int) round($carbs),
            'fat'     => (int) round($fat),
            'pPct'    => $pPct,
            'cPct'    => $cPct,
            'fPct'    => $fPct,
            'totalKcal' => (int) round($totalKcal),
        ];
    }
}
