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

        Equipment::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $imageUrl,
            'requires_gym' => $request->has('requires_gym') ? 1 : 0,
        ]);

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
}
