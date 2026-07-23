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
    Route::post('/clientes/{id}/assign-trainer', [AdminController::class, 'assignTrainer'])->name('clientes.assign_trainer');

    // Rutinas routes
    Route::get('/rutinas', [AdminController::class, 'rutinas'])->name('rutinas.index');
    Route::get('/rutinas/crear', [AdminController::class, 'crearRutina'])->name('rutinas.crear');
    Route::post('/rutinas', [AdminController::class, 'storeRutina'])->name('rutinas.store');
    Route::put('/rutinas/{id}', [AdminController::class, 'updateRutina'])->name('rutinas.update_info');
    Route::get('/rutinas/{id}/ejercicios', [AdminController::class, 'editEjercicios'])->name('rutinas.ejercicios');
    Route::post('/rutinas/{id}/ejercicios', [AdminController::class, 'addEjercicio'])->name('rutinas.add_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/update', [AdminController::class, 'updateEjercicio'])->name('rutinas.update_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/remove', [AdminController::class, 'removeEjercicio'])->name('rutinas.remove_ejercicio');
    Route::post('/rutinas/{id}/assign', [AdminController::class, 'assignRoutineToUser'])->name('rutinas.assign');

    // Nutricion routes
    Route::get('/nutricion', [AdminController::class, 'nutricion'])->name('nutricion.index');
    Route::get('/nutricion/crear', [AdminController::class, 'crearNutricion'])->name('nutricion.crear');
    Route::post('/nutricion', [AdminController::class, 'storeNutricion'])->name('nutricion.store');
    Route::put('/nutricion/{id}', [AdminController::class, 'updateNutricion'])->name('nutricion.update_info');
    Route::get('/nutricion/{id}/comidas', [AdminController::class, 'showComidas'])->name('nutricion.comidas');
    Route::post('/nutricion/{id}/comidas/add-day', [AdminController::class, 'addMealPlanDay'])->name('nutricion.add_meal_plan_day');
    Route::post('/nutricion/{id}/comidas/save', [AdminController::class, 'saveComidasDay'])->name('nutricion.save_comidas_day');
    Route::delete('/nutricion/{id}/comidas/{day_id}', [AdminController::class, 'deleteMealPlanDay'])->name('nutricion.delete_meal_plan_day');
    Route::post('/nutricion/{id}/comidas/{day_id}/remove-meal', [AdminController::class, 'removeMealFromDay'])->name('nutricion.remove_meal');
    Route::post('/nutricion/{id}/assign', [AdminController::class, 'assignMealPlanToUser'])->name('nutricion.assign');

    // Finanzas & Membresías routes (restricted to admin/superadmin in controller constructor)
    Route::get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');
    Route::post('/finanzas/planes', [FinanceController::class, 'storePlan'])->name('finanzas.store_plan');
    Route::put('/finanzas/planes/{id}', [FinanceController::class, 'updatePlan'])->name('finanzas.update_plan');
    Route::post('/finanzas/planes/{id}/toggle', [FinanceController::class, 'togglePlan'])->name('finanzas.toggle_plan');
    Route::post('/finanzas/pagos', [FinanceController::class, 'recordPayment'])->name('finanzas.record_payment');
    Route::post('/finanzas/renovar', [FinanceController::class, 'renewMembership'])->name('finanzas.renew_membership');
    Route::post('/finanzas/promos', [FinanceController::class, 'storePromoCode'])->name('finanzas.store_promo');
    Route::post('/finanzas/promos/{id}/toggle', [FinanceController::class, 'togglePromoCode'])->name('finanzas.toggle_promo');
    Route::get('/api/promos/validate', [FinanceController::class, 'validatePromo'])->name('api.promos.validate');

    // Tienda & Inventario (POS open to trainers/admins, catalog and sales history restricted to admins)
    Route::get('/tienda/pos', [InventoryController::class, 'pos'])->name('tienda.pos');
    Route::post('/tienda/pos', [InventoryController::class, 'registerSale'])->name('tienda.register_sale');
    Route::get('/tienda/productos', [InventoryController::class, 'products'])->name('tienda.products');
    Route::post('/tienda/productos', [InventoryController::class, 'storeProduct'])->name('tienda.store_product');
    Route::put('/tienda/productos/{id}', [InventoryController::class, 'updateProduct'])->name('tienda.update_product');
    Route::delete('/tienda/productos/{id}', [InventoryController::class, 'deleteProduct'])->name('tienda.delete_product');
    Route::get('/tienda/movimientos', [InventoryController::class, 'stockMovements'])->name('tienda.stock_movements');
    Route::post('/tienda/categorias', [InventoryController::class, 'storeCategory'])->name('tienda.store_category');
    Route::post('/tienda/productos/{id}/stock', [InventoryController::class, 'addStock'])->name('tienda.add_stock');
    Route::get('/tienda/ventas', [InventoryController::class, 'salesHistory'])->name('tienda.sales_history');

    // Equipamiento & Catálogos (open to trainers/admins for logistics/programming)
    Route::get('/equipamiento', [CatalogController::class, 'equipment'])->name('catalogos.equipment');
    Route::post('/equipamiento', [CatalogController::class, 'storeEquipment'])->name('catalogos.store_equipment');
    Route::put('/equipamiento/{id}', [CatalogController::class, 'updateEquipment'])->name('catalogos.update_equipment');
    Route::delete('/equipamiento/{id}', [CatalogController::class, 'deleteEquipment'])->name('catalogos.delete_equipment');
    Route::get('/ingredientes', [CatalogController::class, 'ingredients'])->name('catalogos.ingredients');
    Route::post('/ingredientes', [CatalogController::class, 'storeIngredient'])->name('catalogos.store_ingredient');
    Route::put('/ingredientes/{id}', [CatalogController::class, 'updateIngredient'])->name('catalogos.update_ingredient');
    Route::delete('/ingredientes/{id}', [CatalogController::class, 'deleteIngredient'])->name('catalogos.delete_ingredient');

    Route::get('/ejercicios', [CatalogController::class, 'exercises'])->name('catalogos.exercises');
    Route::post('/ejercicios', [CatalogController::class, 'storeExercise'])->name('catalogos.store_exercise');
    Route::put('/ejercicios/{id}', [CatalogController::class, 'updateExercise'])->name('catalogos.update_exercise');
    Route::delete('/ejercicios/{id}', [CatalogController::class, 'deleteExercise'])->name('catalogos.delete_exercise');
    Route::post('/ejercicios/categorias', [CatalogController::class, 'storeExerciseCategory'])->name('catalogos.store_exercise_category');

    Route::get('/recetas', [CatalogController::class, 'recipes'])->name('catalogos.recipes');
    Route::post('/recetas', [CatalogController::class, 'storeRecipe'])->name('catalogos.store_recipe');
    Route::put('/recetas/{id}', [CatalogController::class, 'updateRecipe'])->name('catalogos.update_recipe');
    Route::delete('/recetas/{id}', [CatalogController::class, 'deleteRecipe'])->name('catalogos.delete_recipe');
    Route::post('/recetas/categorias', [CatalogController::class, 'storeRecipeCategory'])->name('catalogos.store_recipe_category');

    // Notificaciones routes
    Route::get('/api/notifications/unread', [AdminController::class, 'getUnreadNotifications'])->name('api.notifications.unread');
    Route::get('/api/aforo', [AdminController::class, 'getAforoApi'])->name('api.aforo');
    Route::get('/notificaciones', [AdminController::class, 'notificationsHistory'])->name('notificaciones.index');
    Route::get('/notificaciones/{id}/read', [AdminController::class, 'readAndRedirect'])->name('notificaciones.read_and_redirect');
    Route::post('/notificaciones/read-all', [AdminController::class, 'markAllAsRead'])->name('notificaciones.read_all');

    // Ruta de prueba temporal para generar notificaciones
    Route::get('/generar-notificacion-prueba', function () {
        $user = auth()->user();
        if (!$user) {
            return "Debes iniciar sesión primero.";
        }

        $roleLabel = ($user->role === 'superadmin') ? 'SuperAdmin' : (($user->role === 'admin') ? 'Administrador' : 'Entrenador');
        $title = ($user->role === 'superadmin')
            ? 'Nueva Sucursal Registrada'
            : (($user->role === 'admin') ? 'Recordatorio: Membresía por vencer' : 'Nueva Rutina Asignada');
        $body = ($user->role === 'superadmin')
            ? 'El gimnasio "Iron Muscle S.A." ha registrado una nueva sucursal en Barcelona y espera aprobación.'
            : (($user->role === 'admin') ? 'El socio "Juan Pérez" tiene su suscripción activa próxima a vencer en 3 días.' : 'Se te ha asignado el entrenamiento de la tarde del socio "María Gómez".');
        $type = ($user->role === 'superadmin') ? 'general' : (($user->role === 'admin') ? 'membership_expiry' : 'new_routine');

        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'is_read' => 0
        ]);

        return redirect()->back()->with('success', "¡Notificación de prueba para rol [$roleLabel] generada con éxito! Revisa la campana en la barra superior.");
    });

    // Staff / Entrenadores routes (restricted to admin/superadmin)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{id}/toggle', [StaffController::class, 'toggleStatus'])->name('staff.toggle_status');

    // Superadmin context switcher route
    Route::post('/superadmin/switch-gym', [AuthController::class, 'switchGym'])->name('superadmin.switch_gym');

    // Superadmin sucursales management routes
    Route::get('/superadmin/gyms', [\App\Http\Controllers\GymController::class, 'index'])->name('superadmin.gyms.index');
    Route::post('/superadmin/gyms', [\App\Http\Controllers\GymController::class, 'store'])->name('superadmin.gyms.store');
    Route::put('/superadmin/gyms/{id}', [\App\Http\Controllers\GymController::class, 'update'])->name('superadmin.gyms.update');
    Route::post('/superadmin/gyms/{id}/toggle', [\App\Http\Controllers\GymController::class, 'toggleStatus'])->name('superadmin.gyms.toggle');
    Route::delete('/superadmin/gyms/{id}', [\App\Http\Controllers\GymController::class, 'destroy'])->name('superadmin.gyms.destroy');

    // Superadmin SaaS subscription plans routes
    Route::get('/superadmin/planes', [\App\Http\Controllers\GymController::class, 'plansIndex'])->name('superadmin.plans.index');
    Route::post('/superadmin/planes', [\App\Http\Controllers\GymController::class, 'plansStore'])->name('superadmin.plans.store');
    Route::put('/superadmin/planes/{id}', [\App\Http\Controllers\GymController::class, 'plansUpdate'])->name('superadmin.plans.update');
    Route::post('/superadmin/planes/{id}/toggle', [\App\Http\Controllers\GymController::class, 'plansToggle'])->name('superadmin.plans.toggle');
    Route::delete('/superadmin/planes/{id}', [\App\Http\Controllers\GymController::class, 'plansDestroy'])->name('superadmin.plans.destroy');

    // Superadmin audit logs route
    Route::get('/superadmin/auditoria', [\App\Http\Controllers\GymController::class, 'auditLogsIndex'])->name('superadmin.audit.index');

    // Global search route
    Route::get('/search', [AdminController::class, 'globalSearch'])->name('global.search');
    Route::get('/api/search/live', [AdminController::class, 'liveSearch'])->name('api.search.live');

    // Attendance routes
    Route::get('/asistencia', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('asistencia.check_in');
    Route::post('/asistencia/{id}/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('asistencia.check_out');
    Route::get('/api/clientes/search-dni', [\App\Http\Controllers\AttendanceController::class, 'searchClientsByDni'])->name('api.clientes.search_dni');
    Route::get('/api/asistencia/logs', [\App\Http\Controllers\AttendanceController::class, 'getLogsByDate'])->name('api.asistencia.logs');

    // Group Classes routes
    Route::get('/clases', [\App\Http\Controllers\ClassController::class, 'index'])->name('clases.index');
    Route::post('/clases', [\App\Http\Controllers\ClassController::class, 'storeClass'])->name('clases.store');
    Route::post('/clases/horarios', [\App\Http\Controllers\ClassController::class, 'storeSchedule'])->name('clases.store_schedule');
    Route::get('/clases/horarios/{id}/reservas', [\App\Http\Controllers\ClassController::class, 'bookings'])->name('clases.bookings');
    Route::post('/clases/horarios/reservar', [\App\Http\Controllers\ClassController::class, 'bookClient'])->name('clases.book_client');
    Route::post('/clases/reservas/{id}/estado', [\App\Http\Controllers\ClassController::class, 'updateBookingStatus'])->name('clases.update_booking_status');

    // Gamification routes
    Route::get('/retos', [\App\Http\Controllers\GamificationController::class, 'index'])->name('retos.index');
    Route::post('/retos', [\App\Http\Controllers\GamificationController::class, 'storeChallenge'])->name('retos.store_challenge');
    Route::post('/retos/medallas', [\App\Http\Controllers\GamificationController::class, 'storeAchievement'])->name('retos.store_achievement');
    Route::get('/retos/{id}/participantes', [\App\Http\Controllers\GamificationController::class, 'challengeParticipants'])->name('retos.participants');
    Route::post('/retos/inscribir', [\App\Http\Controllers\GamificationController::class, 'enrollParticipant'])->name('retos.enroll_participant');
    Route::post('/retos/participantes/{id}/actualizar', [\App\Http\Controllers\GamificationController::class, 'updateParticipant'])->name('retos.update_participant');
    Route::post('/retos/medallas/otorgar', [\App\Http\Controllers\GamificationController::class, 'awardAchievementToUser'])->name('retos.award_achievement');
});
