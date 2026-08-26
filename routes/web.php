<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CashClosingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\PermissionController;

// Public Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel routes
Route::middleware(['auth'])->group(function () {
    // Smart Root Redirect based on user permissions
    Route::get('/', function () {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->hasPermission('dashboard.view')) {
            return redirect()->route('dashboard');
        }
        if ($user->hasPermission('tienda.pos_access')) {
            return redirect()->route('tienda.pos');
        }
        if ($user->hasPermission('asistencia.view')) {
            return redirect()->route('asistencia.index');
        }
        if ($user->hasPermission('clientes.view')) {
            return redirect()->route('clientes.index');
        }
        if ($user->hasPermission('rutinas.view')) {
            return redirect()->route('rutinas.index');
        }
        if ($user->hasPermission('nutricion.view')) {
            return redirect()->route('nutricion.index');
        }
        if ($user->hasPermission('clases.manage')) {
            return redirect()->route('clases.index');
        }
        if ($user->hasPermission('retos.manage')) {
            return redirect()->route('retos.index');
        }
        if ($user->hasPermission('tienda.products_view')) {
            return redirect()->route('tienda.products');
        }
        if ($user->hasPermission('finanzas.view')) {
            return redirect()->route('finanzas.index');
        }
        if ($user->hasPermission('cierre_caja.view')) {
            return redirect()->route('cierre_caja.index');
        }
        if ($user->hasPermission('staff.view')) {
            return redirect()->route('staff.index');
        }
        if ($user->hasPermission('cajeros.view')) {
            return redirect()->route('cajeros.index');
        }
        return redirect()->route('dashboard');
    });

    // Dashboard routes (permission: dashboard.view)
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/dashboard/api/attendance', [AdminController::class, 'apiAttendanceData'])->middleware('permission:dashboard.view')->name('dashboard.api.attendance');
    Route::get('/dashboard/api/traffic', [AdminController::class, 'apiTrafficData'])->middleware('permission:dashboard.view')->name('dashboard.api.traffic');

    // Clientes routes
    Route::get('/clientes', [AdminController::class, 'clientes'])->middleware('permission:clientes.view')->name('clientes.index');
    Route::get('/clientes/crear', [AdminController::class, 'crearCliente'])->middleware('permission:clientes.create')->name('clientes.crear');
    Route::post('/clientes', [AdminController::class, 'storeCliente'])->middleware('permission:clientes.create')->name('clientes.store');
    Route::get('/api/consultar-cne', [AdminController::class, 'consultarCne'])->middleware('permission:clientes.create')->name('api.consultar_cne');
    Route::get('/clientes/{id}', [AdminController::class, 'showCliente'])->middleware('permission:clientes.view')->name('clientes.show');
    Route::get('/clientes/{id}/carnet', [AdminController::class, 'digitalCarnet'])->middleware('permission:clientes.view')->name('clientes.carnet');
    Route::post('/clientes/{id}/assign-routine', [AdminController::class, 'assignRoutine'])->middleware('permission:rutinas.manage')->name('clientes.assign_routine');
    Route::post('/clientes/{id}/assign-meal-plan', [AdminController::class, 'assignMealPlan'])->middleware('permission:nutricion.manage')->name('clientes.assign_meal_plan');
    Route::post('/clientes/{id}/assign-trainer', [AdminController::class, 'assignTrainer'])->middleware('permission:clientes.assign_trainer')->name('clientes.assign_trainer');

    // Rutinas routes
    Route::get('/rutinas', [AdminController::class, 'rutinas'])->middleware('permission:rutinas.view')->name('rutinas.index');
    Route::get('/rutinas/crear', [AdminController::class, 'crearRutina'])->middleware('permission:rutinas.manage')->name('rutinas.crear');
    Route::post('/rutinas', [AdminController::class, 'storeRutina'])->middleware('permission:rutinas.manage')->name('rutinas.store');
    Route::put('/rutinas/{id}', [AdminController::class, 'updateRutina'])->middleware('permission:rutinas.manage')->name('rutinas.update_info');
    Route::get('/rutinas/{id}/ejercicios', [AdminController::class, 'editEjercicios'])->middleware('permission:rutinas.view')->name('rutinas.ejercicios');
    Route::post('/rutinas/{id}/ejercicios', [AdminController::class, 'addEjercicio'])->middleware('permission:rutinas.manage')->name('rutinas.add_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/update', [AdminController::class, 'updateEjercicio'])->middleware('permission:rutinas.manage')->name('rutinas.update_ejercicio');
    Route::post('/rutinas/{id}/ejercicios/{routine_exercise_id}/remove', [AdminController::class, 'removeEjercicio'])->middleware('permission:rutinas.manage')->name('rutinas.remove_ejercicio');
    Route::post('/rutinas/{id}/assign', [AdminController::class, 'assignRoutineToUser'])->middleware('permission:rutinas.manage')->name('rutinas.assign');

    // Nutricion routes
    Route::get('/nutricion', [AdminController::class, 'nutricion'])->middleware('permission:nutricion.view')->name('nutricion.index');
    Route::get('/nutricion/crear', [AdminController::class, 'crearNutricion'])->middleware('permission:nutricion.manage')->name('nutricion.crear');
    Route::post('/nutricion', [AdminController::class, 'storeNutricion'])->middleware('permission:nutricion.manage')->name('nutricion.store');
    Route::put('/nutricion/{id}', [AdminController::class, 'updateNutricion'])->middleware('permission:nutricion.manage')->name('nutricion.update_info');
    Route::get('/nutricion/{id}/comidas', [AdminController::class, 'showComidas'])->middleware('permission:nutricion.view')->name('nutricion.comidas');
    Route::post('/nutricion/{id}/comidas/add-day', [AdminController::class, 'addMealPlanDay'])->middleware('permission:nutricion.manage')->name('nutricion.add_meal_plan_day');
    Route::post('/nutricion/{id}/comidas/save', [AdminController::class, 'saveComidasDay'])->middleware('permission:nutricion.manage')->name('nutricion.save_comidas_day');
    Route::delete('/nutricion/{id}/comidas/{day_id}', [AdminController::class, 'deleteMealPlanDay'])->middleware('permission:nutricion.manage')->name('nutricion.delete_meal_plan_day');
    Route::post('/nutricion/{id}/comidas/{day_id}/remove-meal', [AdminController::class, 'removeMealFromDay'])->middleware('permission:nutricion.manage')->name('nutricion.remove_meal');
    Route::post('/nutricion/{id}/assign', [AdminController::class, 'assignMealPlanToUser'])->middleware('permission:nutricion.manage')->name('nutricion.assign');

    // Finanzas & Membresías routes
    Route::get('/finanzas', [FinanceController::class, 'index'])->middleware('permission:finanzas.view')->name('finanzas.index');
    Route::get('/finanzas/export', [FinanceController::class, 'exportExcel'])->middleware('permission:finanzas.export_reports')->name('finanzas.export');
    Route::post('/finanzas/planes', [FinanceController::class, 'storePlan'])->middleware('permission:finanzas.plans_manage')->name('finanzas.store_plan');
    Route::put('/finanzas/planes/{id}', [FinanceController::class, 'updatePlan'])->middleware('permission:finanzas.plans_manage')->name('finanzas.update_plan');
    Route::post('/finanzas/planes/{id}/toggle', [FinanceController::class, 'togglePlan'])->middleware('permission:finanzas.plans_manage')->name('finanzas.toggle_plan');
    Route::post('/finanzas/pagos', [FinanceController::class, 'recordPayment'])->middleware('permission:finanzas.record_payment')->name('finanzas.record_payment');
    Route::post('/finanzas/pagos/{id}/aprobar', [FinanceController::class, 'approvePendingPayment'])->middleware('permission:finanzas.approve_payments')->name('finanzas.approve_payment');
    Route::post('/finanzas/pagos/{id}/rechazar', [FinanceController::class, 'rejectPendingPayment'])->middleware('permission:finanzas.approve_payments')->name('finanzas.reject_payment');
    Route::post('/finanzas/abonos', [FinanceController::class, 'recordAbono'])->middleware('permission:finanzas.record_payment')->name('finanzas.record_abono');
    Route::post('/finanzas/renovar', [FinanceController::class, 'renewMembership'])->middleware('permission:finanzas.record_payment')->name('finanzas.renew_membership');
    Route::post('/finanzas/promos', [FinanceController::class, 'storePromoCode'])->middleware('permission:finanzas.plans_manage')->name('finanzas.store_promo');
    Route::post('/finanzas/promos/{id}/toggle', [FinanceController::class, 'togglePromoCode'])->middleware('permission:finanzas.plans_manage')->name('finanzas.toggle_promo');
    Route::get('/api/promos/validate', [FinanceController::class, 'validatePromo'])->name('api.promos.validate');

    // Pasarelas de Pago de Gimnasios (gym_payment_gateways)
    Route::get('/finanzas/pasarelas', [PaymentGatewayController::class, 'index'])->middleware('permission:finanzas.gateways_manage')->name('pasarelas.index');
    Route::post('/finanzas/pasarelas', [PaymentGatewayController::class, 'store'])->middleware('permission:finanzas.gateways_manage')->name('pasarelas.store');
    Route::put('/finanzas/pasarelas/{id}', [PaymentGatewayController::class, 'update'])->middleware('permission:finanzas.gateways_manage')->name('pasarelas.update');
    Route::post('/finanzas/pasarelas/{id}/toggle', [PaymentGatewayController::class, 'toggleStatus'])->middleware('permission:finanzas.gateways_manage')->name('pasarelas.toggle');
    Route::delete('/finanzas/pasarelas/{id}', [PaymentGatewayController::class, 'destroy'])->middleware('permission:finanzas.gateways_manage')->name('pasarelas.destroy');

    // Promociones del Gym (Paquetes y descuentos por meses seguidos)
    Route::post('/finanzas/promociones-gym', [FinanceController::class, 'storeGymPromotion'])->middleware('permission:finanzas.plans_manage')->name('finanzas.store_gym_promo');
    Route::post('/finanzas/promociones-gym/{id}/toggle', [FinanceController::class, 'toggleGymPromotion'])->middleware('permission:finanzas.plans_manage')->name('finanzas.toggle_gym_promo');
    Route::delete('/finanzas/promociones-gym/{id}', [FinanceController::class, 'deleteGymPromotion'])->middleware('permission:finanzas.plans_manage')->name('finanzas.delete_gym_promo');

    // Tasa de Cambio (Factor VES) y Sincronización BCV
    Route::get('/finanzas/tasa-cambio', [ExchangeRateController::class, 'index'])->middleware('permission:finanzas.exchange_rate_manage')->name('tasas_cambio.index');
    Route::post('/finanzas/tasa-cambio/sync-bcv', [ExchangeRateController::class, 'syncNow'])->middleware('permission:finanzas.exchange_rate_manage')->name('tasas_cambio.sync_now');
    Route::post('/finanzas/tasa-cambio/manual', [ExchangeRateController::class, 'storeManual'])->middleware('permission:finanzas.exchange_rate_manage')->name('tasas_cambio.store_manual');
    Route::post('/finanzas/tasa-cambio/config', [ExchangeRateController::class, 'updateConfig'])->middleware('permission:finanzas.exchange_rate_manage')->name('tasas_cambio.update_config');
    Route::get('/api/v1/exchange-rate/current', [ExchangeRateController::class, 'apiCurrentRate'])->name('api.v1.exchange_rate.current');

    // Cierre de Caja y Balance Diario
    Route::get('/cierre-caja', [CashClosingController::class, 'index'])->middleware('permission:cierre_caja.view')->name('cierre_caja.index');
    Route::get('/cierre-caja/pdf', [CashClosingController::class, 'exportPdf'])->middleware('permission:cierre_caja.view')->name('cierre_caja.export_pdf');
    Route::post('/cierre-caja/cerrar', [CashClosingController::class, 'closeDay'])->middleware('permission:cierre_caja.close_day')->name('cierre_caja.close_day');

    // Tienda & Inventario (POS, Catálogo, Movimientos y Ventas)
    Route::get('/tienda/pos', [InventoryController::class, 'pos'])->middleware('permission:tienda.pos_access')->name('tienda.pos');
    Route::post('/tienda/pos', [InventoryController::class, 'registerSale'])->middleware('permission:tienda.pos_access')->name('tienda.register_sale');
    Route::get('/tienda/productos', [InventoryController::class, 'products'])->middleware('permission:tienda.products_view')->name('tienda.products');
    Route::post('/tienda/productos', [InventoryController::class, 'storeProduct'])->middleware('permission:tienda.products_manage')->name('tienda.store_product');
    Route::put('/tienda/productos/{id}', [InventoryController::class, 'updateProduct'])->middleware('permission:tienda.products_manage')->name('tienda.update_product');
    Route::delete('/tienda/productos/{id}', [InventoryController::class, 'deleteProduct'])->middleware('permission:tienda.products_manage')->name('tienda.delete_product');
    Route::get('/tienda/movimientos', [InventoryController::class, 'stockMovements'])->middleware('permission:tienda.products_view')->name('tienda.stock_movements');
    Route::post('/tienda/categorias', [InventoryController::class, 'storeCategory'])->middleware('permission:tienda.products_manage')->name('tienda.store_category');
    Route::post('/tienda/productos/{id}/stock', [InventoryController::class, 'addStock'])->middleware('permission:tienda.stock_adjust')->name('tienda.add_stock');
    Route::get('/tienda/productos/{id}/barcode', [InventoryController::class, 'getProductBarcode'])->middleware('permission:tienda.products_view')->name('tienda.product_barcode');
    Route::post('/tienda/pos/send-email', [InventoryController::class, 'sendReceiptEmail'])->middleware('permission:tienda.pos_access')->name('tienda.send_email');
    Route::get('/tienda/ventas', [InventoryController::class, 'salesHistory'])->middleware('permission:tienda.sales_history_view')->name('tienda.sales_history');

    // Equipamiento & Catálogos
    Route::get('/equipamiento', [CatalogController::class, 'equipment'])->middleware('permission:catalogos.manage')->name('catalogos.equipment');
    Route::post('/equipamiento', [CatalogController::class, 'storeEquipment'])->middleware('permission:catalogos.manage')->name('catalogos.store_equipment');
    Route::put('/equipamiento/{id}', [CatalogController::class, 'updateEquipment'])->middleware('permission:catalogos.manage')->name('catalogos.update_equipment');
    Route::delete('/equipamiento/{id}', [CatalogController::class, 'deleteEquipment'])->middleware('permission:catalogos.manage')->name('catalogos.delete_equipment');
    Route::get('/ingredientes', [CatalogController::class, 'ingredients'])->middleware('permission:catalogos.manage')->name('catalogos.ingredients');
    Route::post('/ingredientes', [CatalogController::class, 'storeIngredient'])->middleware('permission:catalogos.manage')->name('catalogos.store_ingredient');
    Route::put('/ingredientes/{id}', [CatalogController::class, 'updateIngredient'])->middleware('permission:catalogos.manage')->name('catalogos.update_ingredient');
    Route::delete('/ingredientes/{id}', [CatalogController::class, 'deleteIngredient'])->middleware('permission:catalogos.manage')->name('catalogos.delete_ingredient');

    Route::get('/ejercicios', [CatalogController::class, 'exercises'])->middleware('permission:catalogos.manage')->name('catalogos.exercises');
    Route::post('/ejercicios', [CatalogController::class, 'storeExercise'])->middleware('permission:catalogos.manage')->name('catalogos.store_exercise');
    Route::put('/ejercicios/{id}', [CatalogController::class, 'updateExercise'])->middleware('permission:catalogos.manage')->name('catalogos.update_exercise');
    Route::delete('/ejercicios/{id}', [CatalogController::class, 'deleteExercise'])->middleware('permission:catalogos.manage')->name('catalogos.delete_exercise');
    Route::post('/ejercicios/categorias', [CatalogController::class, 'storeExerciseCategory'])->middleware('permission:catalogos.manage')->name('catalogos.store_exercise_category');

    Route::get('/recetas', [CatalogController::class, 'recipes'])->middleware('permission:catalogos.manage')->name('catalogos.recipes');
    Route::post('/recetas', [CatalogController::class, 'storeRecipe'])->middleware('permission:catalogos.manage')->name('catalogos.store_recipe');
    Route::put('/recetas/{id}', [CatalogController::class, 'updateRecipe'])->middleware('permission:catalogos.manage')->name('catalogos.update_recipe');
    Route::delete('/recetas/{id}', [CatalogController::class, 'deleteRecipe'])->middleware('permission:catalogos.manage')->name('catalogos.delete_recipe');
    Route::post('/recetas/categorias', [CatalogController::class, 'storeRecipeCategory'])->middleware('permission:catalogos.manage')->name('catalogos.store_recipe_category');

    // API y Pasarelas de Pago
    Route::get('/api/v1/gyms/{gym_id}/payment-gateways', [PaymentGatewayController::class, 'apiGetGymGateways'])->name('api.v1.gym_payment_gateways');
    Route::post('/api/v1/payments/submit-proof', [FinanceController::class, 'apiSubmitPaymentProof'])->name('api.v1.payments.submit_proof');
    Route::get('/api/notifications/unread', [AdminController::class, 'getUnreadNotifications'])->name('api.notifications.unread');
    Route::get('/api/aforo', [AdminController::class, 'getAforoApi'])->name('api.aforo');
    Route::get('/notificaciones/{id}/read', [AdminController::class, 'readAndRedirect'])->name('notificaciones.read_and_redirect');

    // Staff / Entrenadores routes
    Route::get('/staff', [StaffController::class, 'index'])->middleware('permission:staff.view')->name('staff.index');
    Route::get('/staff/{id}/detalles', [StaffController::class, 'showDetails'])->middleware('permission:staff.view')->name('staff.show_details');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('permission:staff.manage')->name('staff.store');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->middleware('permission:staff.manage')->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->middleware('permission:staff.manage')->name('staff.destroy');
    Route::post('/staff/{id}/toggle', [StaffController::class, 'toggleStatus'])->middleware('permission:staff.manage')->name('staff.toggle_status');

    // Cajeros / Personal de Caja routes
    Route::get('/cajeros', [CashierController::class, 'index'])->middleware('permission:cajeros.view')->name('cajeros.index');
    Route::get('/cajeros/{id}/detalles', [CashierController::class, 'showDetails'])->middleware('permission:cajeros.view')->name('cajeros.show_details');
    Route::post('/cajeros', [CashierController::class, 'store'])->middleware('permission:cajeros.manage')->name('cajeros.store');
    Route::put('/cajeros/{id}', [CashierController::class, 'update'])->middleware('permission:cajeros.manage')->name('cajeros.update');
    Route::delete('/cajeros/{id}', [CashierController::class, 'destroy'])->middleware('permission:cajeros.manage')->name('cajeros.destroy');
    Route::post('/cajeros/{id}/toggle', [CashierController::class, 'toggleStatus'])->middleware('permission:cajeros.manage')->name('cajeros.toggle_status');

    // Gestión Granular de Permisos y Roles (RBAC & User Overrides)
    Route::get('/permisos', [PermissionController::class, 'index'])->middleware('permission:permisos.manage')->name('permisos.index');
    Route::post('/permisos/roles', [PermissionController::class, 'updateRole'])->middleware('permission:permisos.manage')->name('permisos.update_role');
    Route::get('/permisos/users/{id}', [PermissionController::class, 'getUserPermissions'])->middleware('permission:permisos.manage')->name('permisos.get_user');
    Route::post('/permisos/users/{id}', [PermissionController::class, 'updateUserPermissions'])->middleware('permission:permisos.manage')->name('permisos.update_user');
    Route::post('/permisos/users/{id}/reset', [PermissionController::class, 'resetUser'])->middleware('permission:permisos.manage')->name('permisos.reset_user');

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
    Route::get('/asistencia', [\App\Http\Controllers\AttendanceController::class, 'index'])->middleware('permission:asistencia.view')->name('asistencia.index');
    Route::post('/asistencia/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])->middleware('permission:asistencia.check_in_out')->name('asistencia.check_in');
    Route::post('/asistencia/{id}/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->middleware('permission:asistencia.check_in_out')->name('asistencia.check_out');
    Route::get('/api/clientes/search-dni', [\App\Http\Controllers\AttendanceController::class, 'searchClientsByDni'])->middleware('permission:asistencia.check_in_out')->name('api.clientes.search_dni');
    Route::get('/api/asistencia/logs', [\App\Http\Controllers\AttendanceController::class, 'getLogsByDate'])->middleware('permission:asistencia.view')->name('api.asistencia.logs');

    // Group Classes routes
    Route::get('/clases', [\App\Http\Controllers\ClassController::class, 'index'])->middleware('permission:clases.manage')->name('clases.index');
    Route::post('/clases', [\App\Http\Controllers\ClassController::class, 'storeClass'])->middleware('permission:clases.manage')->name('clases.store');
    Route::put('/clases/{id}', [\App\Http\Controllers\ClassController::class, 'updateClass'])->middleware('permission:clases.manage')->name('clases.update');
    Route::delete('/clases/{id}', [\App\Http\Controllers\ClassController::class, 'deleteClass'])->middleware('permission:clases.manage')->name('clases.delete');
    Route::post('/clases/horarios', [\App\Http\Controllers\ClassController::class, 'storeSchedule'])->middleware('permission:clases.manage')->name('clases.store_schedule');
    Route::put('/clases/horarios/{id}', [\App\Http\Controllers\ClassController::class, 'updateSchedule'])->middleware('permission:clases.manage')->name('clases.update_schedule');
    Route::delete('/clases/horarios/{id}', [\App\Http\Controllers\ClassController::class, 'deleteSchedule'])->middleware('permission:clases.manage')->name('clases.delete_schedule');
    Route::get('/clases/horarios/{id}/reservas', [\App\Http\Controllers\ClassController::class, 'bookings'])->middleware('permission:clases.manage')->name('clases.bookings');
    Route::post('/clases/horarios/reservar', [\App\Http\Controllers\ClassController::class, 'bookClient'])->middleware('permission:clases.manage')->name('clases.book_client');
    Route::post('/clases/reservas/{id}/estado', [\App\Http\Controllers\ClassController::class, 'updateBookingStatus'])->middleware('permission:clases.manage')->name('clases.update_booking_status');

    // Gamification routes
    Route::get('/retos', [\App\Http\Controllers\GamificationController::class, 'index'])->middleware('permission:retos.manage')->name('retos.index');
    Route::post('/retos', [\App\Http\Controllers\GamificationController::class, 'storeChallenge'])->middleware('permission:retos.manage')->name('retos.store_challenge');
    Route::put('/retos/{id}', [\App\Http\Controllers\GamificationController::class, 'updateChallenge'])->middleware('permission:retos.manage')->name('retos.update_challenge');
    Route::delete('/retos/{id}', [\App\Http\Controllers\GamificationController::class, 'deleteChallenge'])->middleware('permission:retos.manage')->name('retos.delete_challenge');
    Route::post('/retos/medallas', [\App\Http\Controllers\GamificationController::class, 'storeAchievement'])->middleware('permission:retos.manage')->name('retos.store_achievement');
    Route::put('/retos/medallas/{id}', [\App\Http\Controllers\GamificationController::class, 'updateAchievement'])->middleware('permission:retos.manage')->name('retos.update_achievement');
    Route::delete('/retos/medallas/{id}', [\App\Http\Controllers\GamificationController::class, 'deleteAchievement'])->middleware('permission:retos.manage')->name('retos.delete_achievement');
    Route::get('/retos/{id}/participantes', [\App\Http\Controllers\GamificationController::class, 'challengeParticipants'])->middleware('permission:retos.manage')->name('retos.participants');
    Route::post('/retos/{id}/evaluar-progreso', [\App\Http\Controllers\GamificationController::class, 'evaluateChallengeProgress'])->middleware('permission:retos.manage')->name('retos.evaluate_progress');
    Route::post('/retos/inscribir', [\App\Http\Controllers\GamificationController::class, 'enrollParticipant'])->middleware('permission:retos.manage')->name('retos.enroll_participant');
    Route::post('/retos/participantes/{id}/actualizar', [\App\Http\Controllers\GamificationController::class, 'updateParticipant'])->middleware('permission:retos.manage')->name('retos.update_participant');
    Route::post('/retos/medallas/otorgar', [\App\Http\Controllers\GamificationController::class, 'awardAchievementToUser'])->middleware('permission:retos.manage')->name('retos.award_achievement');
    Route::post('/retos/medallas/evaluar-automaticos', [\App\Http\Controllers\GamificationController::class, 'evaluateAllAchievements'])->middleware('permission:retos.manage')->name('retos.evaluate_all_achievements');

    // Notification Center routes
    Route::get('/notificaciones', [NotificationController::class, 'index'])->middleware('permission:notificaciones.send')->name('notificaciones.index');
    Route::post('/notificaciones/enviar', [NotificationController::class, 'sendManual'])->middleware('permission:notificaciones.send')->name('notificaciones.send_manual');
    Route::post('/notificaciones/{id}/leer', [NotificationController::class, 'markAsRead'])->name('notificaciones.mark_read');
    Route::post('/notificaciones/marcar-todas', [NotificationController::class, 'markAllAsRead'])->name('notificaciones.mark_all_read');
    Route::post('/notificaciones/read-all', [NotificationController::class, 'markAllAsRead'])->name('notificaciones.read_all');
    Route::delete('/notificaciones/{id}', [NotificationController::class, 'destroy'])->name('notificaciones.destroy');
    Route::post('/notificaciones/limpiar-antiguas', [NotificationController::class, 'cleanupOld'])->name('notificaciones.cleanup_old');
    Route::post('/notificaciones/ejecutar-disparadores', [NotificationController::class, 'runAutoTriggers'])->middleware('permission:notificaciones.send')->name('notificaciones.run_triggers');

    // Settings / Configuración routes
    Route::get('/configuracion', [\App\Http\Controllers\SettingsController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion/font-size', [\App\Http\Controllers\SettingsController::class, 'updateFontSize'])->name('configuracion.update_font_size');
});
