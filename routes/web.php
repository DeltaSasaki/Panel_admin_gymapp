<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\StaffController;

// Public Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Clientes routes
    Route::get('/clientes', [AdminController::class, 'clientes'])->name('clientes.index');
    Route::get('/clientes/crear', [AdminController::class, 'crearCliente'])->name('clientes.crear');
    Route::post('/clientes', [AdminController::class, 'storeCliente'])->name('clientes.store');
    Route::get('/clientes/{id}', [AdminController::class, 'showCliente'])->name('clientes.show');
    Route::post('/clientes/{id}/assign-routine', [AdminController::class, 'assignRoutine'])->name('clientes.assign_routine');
    Route::post('/clientes/{id}/assign-meal-plan', [AdminController::class, 'assignMealPlan'])->name('clientes.assign_meal_plan');

    // Rutinas routes
    Route::get('/rutinas', [AdminController::class, 'rutinas'])->name('rutinas.index');
    Route::get('/rutinas/crear', [AdminController::class, 'crearRutina'])->name('rutinas.crear');
    Route::post('/rutinas', [AdminController::class, 'storeRutina'])->name('rutinas.store');
    Route::get('/rutinas/{id}/ejercicios', [AdminController::class, 'editEjercicios'])->name('rutinas.ejercicios');
    Route::post('/rutinas/{id}/ejercicios', [AdminController::class, 'addEjercicio'])->name('rutinas.add_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/update', [AdminController::class, 'updateEjercicio'])->name('rutinas.update_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/remove', [AdminController::class, 'removeEjercicio'])->name('rutinas.remove_ejercicio');
    Route::post('/rutinas/{id}/assign', [AdminController::class, 'assignRoutineToUser'])->name('rutinas.assign');

    // Nutricion routes
    Route::get('/nutricion', [AdminController::class, 'nutricion'])->name('nutricion.index');
    Route::get('/nutricion/crear', [AdminController::class, 'crearNutricion'])->name('nutricion.crear');
    Route::post('/nutricion', [AdminController::class, 'storeNutricion'])->name('nutricion.store');
    Route::get('/nutricion/{id}/comidas', [AdminController::class, 'showComidas'])->name('nutricion.comidas');
    Route::post('/nutricion/{id}/assign', [AdminController::class, 'assignMealPlanToUser'])->name('nutricion.assign');

    // Finanzas & Membresías routes (restricted to admin/superadmin in controller constructor)
    Route::get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');
    Route::post('/finanzas/planes', [FinanceController::class, 'storePlan'])->name('finanzas.store_plan');
    Route::post('/finanzas/pagos', [FinanceController::class, 'recordPayment'])->name('finanzas.record_payment');
    Route::post('/finanzas/renovar', [FinanceController::class, 'renewMembership'])->name('finanzas.renew_membership');

    // Tienda & Inventario (POS open to trainers/admins, catalog and sales history restricted to admins)
    Route::get('/tienda/pos', [InventoryController::class, 'pos'])->name('tienda.pos');
    Route::post('/tienda/pos', [InventoryController::class, 'registerSale'])->name('tienda.register_sale');
    Route::get('/tienda/productos', [InventoryController::class, 'products'])->name('tienda.products');
    Route::post('/tienda/productos', [InventoryController::class, 'storeProduct'])->name('tienda.store_product');
    Route::post('/tienda/categorias', [InventoryController::class, 'storeCategory'])->name('tienda.store_category');
    Route::post('/tienda/productos/{id}/stock', [InventoryController::class, 'addStock'])->name('tienda.add_stock');
    Route::get('/tienda/ventas', [InventoryController::class, 'salesHistory'])->name('tienda.sales_history');

    // Equipamiento & Catálogos (open to trainers/admins for logistics/programming)
    Route::get('/equipamiento', [CatalogController::class, 'equipment'])->name('catalogos.equipment');
    Route::post('/equipamiento', [CatalogController::class, 'storeEquipment'])->name('catalogos.store_equipment');
    Route::get('/ingredientes', [CatalogController::class, 'ingredients'])->name('catalogos.ingredients');
    Route::post('/ingredientes', [CatalogController::class, 'storeIngredient'])->name('catalogos.store_ingredient');

    // Staff / Entrenadores routes (restricted to admin/superadmin)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::post('/staff/{id}/toggle', [StaffController::class, 'toggleStatus'])->name('staff.toggle_status');
    
    // Superadmin context switcher route
    Route::post('/superadmin/switch-gym', [AuthController::class, 'switchGym'])->name('superadmin.switch_gym');

    // Superadmin sucursales management routes
    Route::get('/superadmin/gyms', [\App\Http\Controllers\GymController::class, 'index'])->name('superadmin.gyms.index');
    Route::post('/superadmin/gyms', [\App\Http\Controllers\GymController::class, 'store'])->name('superadmin.gyms.store');
    Route::put('/superadmin/gyms/{id}', [\App\Http\Controllers\GymController::class, 'update'])->name('superadmin.gyms.update');
    Route::post('/superadmin/gyms/{id}/toggle', [\App\Http\Controllers\GymController::class, 'toggleStatus'])->name('superadmin.gyms.toggle');
    Route::delete('/superadmin/gyms/{id}', [\App\Http\Controllers\GymController::class, 'destroy'])->name('superadmin.gyms.destroy');
});
