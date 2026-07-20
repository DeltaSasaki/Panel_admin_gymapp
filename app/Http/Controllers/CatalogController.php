<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Ingredient;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\AdminAuditLog;

class CatalogController extends Controller
{
    /**
     * Equipment overview.
     */
    public function equipment()
    {
        $gymId = $this->getActiveGymId();
        $equipment = Equipment::where('gym_id', $gymId)->orderBy('name')->get();
        $totalMachines = $equipment->count();

        return view('catalogos.equipamiento', compact('equipment', 'totalMachines'));
    }

    public function storeEquipment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'requires_gym' => 'nullable',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'eq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/equipment'), $filename);
            $imageUrl = 'uploads/equipment/' . $filename;
        }

        $gymId = $this->getActiveGymId();
        $eq = Equipment::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $imageUrl,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
        ]);

        AdminAuditLog::record('INSERT', 'equipment', $eq->id, null, $eq->toArray(), $gymId);

        return redirect()->back()->with('success', 'Equipo registrado con éxito.');
    }

    /**
     * Update existing equipment.
     */
    public function updateEquipment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'requires_gym' => 'nullable',
        ]);

        $gymId = $this->getActiveGymId();
        $equipment = Equipment::where('gym_id', $gymId)->findOrFail($id);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            // Delete old image file if it exists and is local
            if ($equipment->image_url && file_exists(public_path($equipment->image_url))) {
                @unlink(public_path($equipment->image_url));
            }

            $file = $request->file('image');
            $filename = 'eq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/equipment'), $filename);
            $data['image_url'] = 'uploads/equipment/' . $filename;
        } elseif ($request->remove_image == '1') {
            if ($equipment->image_url && file_exists(public_path($equipment->image_url))) {
                @unlink(public_path($equipment->image_url));
            }
            $data['image_url'] = null;
        }

        $equipment->update($data);

        return redirect()->back()->with('success', 'Equipo actualizado con éxito.');
    }

    /**
     * Delete existing equipment.
     */
    public function deleteEquipment($id)
    {
        $gymId = $this->getActiveGymId();
        $equipment = Equipment::where('gym_id', $gymId)->findOrFail($id);

        // Delete image file if exists
        if ($equipment->image_url && file_exists(public_path($equipment->image_url))) {
            @unlink(public_path($equipment->image_url));
        }

        $equipment->delete();

        return redirect()->back()->with('success', 'Equipo eliminado con éxito.');
    }

    /**
     * Ingredients catalog.
     */
    public function ingredients()
    {
        $gymId = $this->getActiveGymId();
        $ingredients = Ingredient::where('gym_id', $gymId)->orderBy('name')->get();

        return view('catalogos.ingredientes', compact('ingredients'));
    }

    /**
     * Store new ingredient.
     */
    public function storeIngredient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'calories_per_100g' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
        ]);

        Ingredient::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'protein_g' => $request->protein_g,
            'carbs_g' => $request->carbs_g,
            'fat_g' => $request->fat_g,
            'calories_per_100g' => $request->calories_per_100g,
            'unit' => $request->unit,
            'is_global' => 1,
        ]);

        return redirect()->back()->with('success', 'Ingrediente alimenticio añadido al catálogo.');
    }

    /**
     * Exercises dictionary.
     */
    public function exercises()
    {
        $gymId = $this->getActiveGymId();
        $exercises = Exercise::where('gym_id', $gymId)
            ->orWhereNull('gym_id') // Allow global exercises too
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = ExerciseCategory::where('gym_id', $gymId)
            ->orWhereNull('gym_id')
            ->orderBy('name')
            ->get();

        return view('catalogos.ejercicios', compact('exercises', 'categories'));
    }

    /**
     * Store new exercise.
     */
    public function storeExercise(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer',
            'muscle_group' => 'nullable|string|max:150',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'requires_equipment' => 'nullable',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'ex_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/exercises'), $filename);
            $imageUrl = 'uploads/exercises/' . $filename;
        }

        Exercise::create([
            'gym_id' => $this->getActiveGymId(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'muscle_group' => $request->muscle_group,
            'difficulty' => $request->difficulty,
            'requires_equipment' => $request->has('requires_equipment') ? 1 : 0,
            'video_url' => $request->video_url,
            'image_url' => $imageUrl,
            'is_global' => 0,
        ]);

        return redirect()->back()->with('success', 'Ejercicio añadido al catálogo con éxito.');
    }

    /**
     * Update existing exercise.
     */
    public function updateExercise(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer',
            'muscle_group' => 'nullable|string|max:150',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'requires_equipment' => 'nullable',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        $gymId = $this->getActiveGymId();
        // Allow updating only gym-specific exercises
        $exercise = Exercise::where('gym_id', $gymId)->findOrFail($id);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'muscle_group' => $request->muscle_group,
            'difficulty' => $request->difficulty,
            'requires_equipment' => $request->has('requires_equipment') ? 1 : 0,
            'video_url' => $request->video_url,
        ];

        if ($request->hasFile('image')) {
            // Delete old image file if it exists and is local
            if ($exercise->image_url && file_exists(public_path($exercise->image_url))) {
                @unlink(public_path($exercise->image_url));
            }

            $file = $request->file('image');
            $filename = 'ex_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/exercises'), $filename);
            $data['image_url'] = 'uploads/exercises/' . $filename;
        } elseif ($request->remove_image == '1') {
            if ($exercise->image_url && file_exists(public_path($exercise->image_url))) {
                @unlink(public_path($exercise->image_url));
            }
            $data['image_url'] = null;
        }

        $exercise->update($data);

        return redirect()->back()->with('success', 'Ejercicio actualizado con éxito.');
    }

    /**
     * Delete existing exercise.
     */
    public function deleteExercise($id)
    {
        $gymId = $this->getActiveGymId();
        $exercise = Exercise::where('gym_id', $gymId)->findOrFail($id);

        // Delete image file if exists
        if ($exercise->image_url && file_exists(public_path($exercise->image_url))) {
            @unlink(public_path($exercise->image_url));
        }

        $exercise->delete();

        return redirect()->back()->with('success', 'Ejercicio eliminado con éxito.');
    }

    /**
     * Store new exercise category.
     */
    public function storeExerciseCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        ExerciseCategory::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Categoría de ejercicio creada con éxito.');
    }

    /**
     * Recipes catalog.
     */
    public function recipes()
    {
        $gymId = $this->getActiveGymId();
        $recipes = Recipe::where('gym_id', $gymId)->with('category')->orderBy('name')->get();
        $categories = RecipeCategory::where('gym_id', $gymId)->orderBy('name')->get();

        return view('catalogos.recetas', compact('recipes', 'categories'));
    }

    /**
     * Store new recipe.
     */
    public function storeRecipe(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'preparation_min' => 'required|integer|min:1',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,general',
            'calories_total' => 'required|numeric|min:0',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'servings' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'rc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/recipes'), $filename);
            $imageUrl = 'uploads/recipes/' . $filename;
        }

        Recipe::create([
            'gym_id' => $this->getActiveGymId(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'preparation_min' => $request->preparation_min,
            'goal_type' => $request->goal_type,
            'calories_total' => $request->calories_total,
            'protein_g' => $request->protein_g,
            'carbs_g' => $request->carbs_g,
            'fat_g' => $request->fat_g,
            'servings' => $request->servings,
            'image_url' => $imageUrl,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Receta guardada con éxito en el recetario.');
    }

    /**
     * Update existing recipe.
     */
    public function updateRecipe(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'preparation_min' => 'required|integer|min:1',
            'goal_type' => 'required|in:lose_weight,gain_muscle,gain_weight,maintain,improve_endurance,general',
            'calories_total' => 'required|numeric|min:0',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'servings' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        $gymId = $this->getActiveGymId();
        $recipe = Recipe::where('gym_id', $gymId)->findOrFail($id);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'preparation_min' => $request->preparation_min,
            'goal_type' => $request->goal_type,
            'calories_total' => $request->calories_total,
            'protein_g' => $request->protein_g,
            'carbs_g' => $request->carbs_g,
            'fat_g' => $request->fat_g,
            'servings' => $request->servings,
        ];

        if ($request->hasFile('image')) {
            if ($recipe->image_url && file_exists(public_path($recipe->image_url))) {
                @unlink(public_path($recipe->image_url));
            }

            $file = $request->file('image');
            $filename = 'rc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/recipes'), $filename);
            $data['image_url'] = 'uploads/recipes/' . $filename;
        } elseif ($request->remove_image == '1') {
            if ($recipe->image_url && file_exists(public_path($recipe->image_url))) {
                @unlink(public_path($recipe->image_url));
            }
            $data['image_url'] = null;
        }

        $recipe->update($data);

        return redirect()->back()->with('success', 'Receta actualizada con éxito.');
    }

    /**
     * Delete existing recipe.
     */
    public function deleteRecipe($id)
    {
        $gymId = $this->getActiveGymId();
        $recipe = Recipe::where('gym_id', $gymId)->findOrFail($id);

        if ($recipe->image_url && file_exists(public_path($recipe->image_url))) {
            @unlink(public_path($recipe->image_url));
        }

        $recipe->delete();

        return redirect()->back()->with('success', 'Receta eliminada con éxito.');
    }

    /**
     * Store new recipe category.
     */
    public function storeRecipeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        RecipeCategory::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Categoría de recetas creada con éxito.');
    }
}
