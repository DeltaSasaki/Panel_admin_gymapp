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
            'is_active' => 1,
        ]);

        AdminAuditLog::record('INSERT', 'equipment', $eq->id, null, $eq->toArray(), $gymId);

        $message = 'Equipo registrado con éxito en el catálogo.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'equipment' => $eq
            ]);
        }

        return redirect()->back()->with('success', $message);
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
        $oldData = $equipment->toArray();

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
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

        AdminAuditLog::record('UPDATE', 'equipment', $equipment->id, $oldData, $equipment->fresh()->toArray(), $gymId);

        $message = 'Equipo actualizado con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'equipment' => $equipment
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable/Toggle active status of existing equipment.
     */
    public function deleteEquipment($id)
    {
        $gymId = $this->getActiveGymId();
        $equipment = Equipment::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $equipment->toArray();

        $newStatus = $equipment->is_active ? 0 : 1;
        $equipment->update(['is_active' => $newStatus]);

        AdminAuditLog::record('UPDATE', 'equipment', $id, $oldData, $equipment->fresh()->toArray(), $gymId);

        $message = $newStatus 
            ? "Equipo '{$equipment->name}' activado con éxito."
            : "Equipo '{$equipment->name}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'equipment_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        $gymId = $this->getActiveGymId();

        $ingredient = Ingredient::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'protein_g' => $request->protein_g,
            'carbs_g' => $request->carbs_g,
            'fat_g' => $request->fat_g,
            'calories_per_100g' => $request->calories_per_100g,
            'unit' => $request->unit,
            'is_global' => 1,
        ]);

        AdminAuditLog::record('INSERT', 'ingredients', $ingredient->id, null, $ingredient->toArray(), $gymId);

        $message = 'Ingrediente alimenticio añadido al catálogo con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'ingredient' => $ingredient
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update existing ingredient.
     */
    public function updateIngredient(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'calories_per_100g' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
        ]);

        $gymId = $this->getActiveGymId();
        $ingredient = Ingredient::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $ingredient->toArray();

        $ingredient->update([
            'name' => $request->name,
            'protein_g' => $request->protein_g,
            'carbs_g' => $request->carbs_g,
            'fat_g' => $request->fat_g,
            'calories_per_100g' => $request->calories_per_100g,
            'unit' => $request->unit,
        ]);

        AdminAuditLog::record('UPDATE', 'ingredients', $ingredient->id, $oldData, $ingredient->fresh()->toArray(), $gymId);

        $message = 'Ingrediente alimenticio actualizado con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'ingredient' => $ingredient
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable or toggle active status of existing ingredient (soft delete / disable).
     */
    public function deleteIngredient($id)
    {
        $gymId = $this->getActiveGymId();
        $ingredient = Ingredient::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $ingredient->toArray();

        $newStatus = $ingredient->is_active ? 0 : 1;
        $ingredient->update(['is_active' => $newStatus]);

        AdminAuditLog::record('UPDATE', 'ingredients', $id, $oldData, $ingredient->fresh()->toArray(), $gymId);

        $message = $newStatus 
            ? "Ingrediente '{$ingredient->name}' activado con éxito."
            : "Ingrediente '{$ingredient->name}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'ingredient_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        $gymId = $this->getActiveGymId();

        $exercise = Exercise::create([
            'gym_id' => $gymId,
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
            'is_active' => 1,
        ]);

        AdminAuditLog::record('INSERT', 'exercises', $exercise->id, null, $exercise->toArray(), $gymId);

        $message = 'Ejercicio añadido al catálogo con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            $exercise->load('category');
            return response()->json([
                'success' => true,
                'message' => $message,
                'exercise' => $exercise
            ]);
        }

        return redirect()->back()->with('success', $message);
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
        $exercise = Exercise::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $exercise->toArray();

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

        AdminAuditLog::record('UPDATE', 'exercises', $exercise->id, $oldData, $exercise->fresh()->toArray(), $gymId);

        $message = 'Ejercicio actualizado con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            $exercise->load('category');
            return response()->json([
                'success' => true,
                'message' => $message,
                'exercise' => $exercise
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable/Toggle active status of existing exercise.
     */
    public function deleteExercise($id)
    {
        $gymId = $this->getActiveGymId();
        $exercise = Exercise::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $exercise->toArray();

        $newStatus = $exercise->is_active ? 0 : 1;
        $exercise->update(['is_active' => $newStatus]);

        AdminAuditLog::record('UPDATE', 'exercises', $id, $oldData, $exercise->fresh()->toArray(), $gymId);

        $message = $newStatus 
            ? "Ejercicio '{$exercise->name}' activado con éxito."
            : "Ejercicio '{$exercise->name}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'exercise_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        $gymId = $this->getActiveGymId();

        $category = ExerciseCategory::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $message = 'Categoría de ejercicio creada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'category' => $category
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        $gymId = $this->getActiveGymId();

        $recipe = Recipe::create([
            'gym_id' => $gymId,
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

        AdminAuditLog::record('INSERT', 'recipes', $recipe->id, null, $recipe->toArray(), $gymId);

        $message = 'Receta guardada con éxito en el recetario.';

        if ($request->ajax() || $request->wantsJson()) {
            $recipe->load('category');
            return response()->json([
                'success' => true,
                'message' => $message,
                'recipe' => $recipe
            ]);
        }

        return redirect()->back()->with('success', $message);
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
        $oldData = $recipe->toArray();

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

        AdminAuditLog::record('UPDATE', 'recipes', $recipe->id, $oldData, $recipe->fresh()->toArray(), $gymId);

        $message = 'Receta actualizada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            $recipe->load('category');
            return response()->json([
                'success' => true,
                'message' => $message,
                'recipe' => $recipe
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable/Toggle active status of existing recipe.
     */
    public function deleteRecipe($id)
    {
        $gymId = $this->getActiveGymId();
        $recipe = Recipe::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $recipe->toArray();

        $newStatus = $recipe->is_active ? 0 : 1;
        $recipe->update(['is_active' => $newStatus]);

        AdminAuditLog::record('UPDATE', 'recipes', $id, $oldData, $recipe->fresh()->toArray(), $gymId);

        $message = $newStatus 
            ? "Receta '{$recipe->name}' activada con éxito."
            : "Receta '{$recipe->name}' inhabilitada con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'recipe_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        $gymId = $this->getActiveGymId();

        $category = RecipeCategory::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $message = 'Categoría de recetas creada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'category' => $category
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
