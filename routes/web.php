<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', [AdminController::class, 'dashboard']);
Route::get('/dashboard', [AdminController::class, 'dashboard']);

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


