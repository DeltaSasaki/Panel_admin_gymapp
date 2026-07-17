<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Ingredient;

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

    /**
     * Store new equipment.
     */
    public function storeEquipment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        Equipment::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'description' => $request->description,
            'requires_gym' => 1,
        ]);

        return redirect()->back()->with('success', 'Equipo registrado con éxito.');
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
}
