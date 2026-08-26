<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table: permissions
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 100)->unique();
                $table->string('name', 150);
                $table->string('module', 50);
                $table->enum('type', ['menu_access', 'action', 'widget'])->default('action');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Table: role_permissions
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('role', 30);
                $table->unsignedInteger('permission_id');
                $table->timestamps();

                $table->unique(['role', 'permission_id'], 'role_permissions_role_permission_id_unique');
                $table->foreign('permission_id', 'role_permissions_permission_id_foreign')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
            });
        }

        // 3. Table: user_permissions
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->unsignedInteger('permission_id');
                $table->boolean('is_granted')->default(1)->comment('1: Concedido explícitamente, 0: Denegado explícitamente');
                $table->timestamps();

                $table->unique(['user_id', 'permission_id'], 'user_permissions_user_id_permission_id_unique');
                $table->foreign('permission_id', 'user_permissions_permission_id_foreign')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
                $table->foreign('user_id', 'user_permissions_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }

        // 4. Seed full permissions catalog and default role permissions
        $this->seedPermissionsCatalog();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }

    /**
     * Seed permissions catalog and initial role mappings.
     */
    protected function seedPermissionsCatalog(): void
    {
        $now = now();

        $catalog = [
            // Dashboard
            [
                'code' => 'dashboard.view',
                'name' => 'Acceso al Dashboard Principal',
                'module' => 'dashboard',
                'type' => 'menu_access',
                'description' => 'Permite visualizar las métricas clave, KPIs de asistencia y gráficos del dashboard principal.',
            ],
            // Clientes
            [
                'code' => 'clientes.view',
                'name' => 'Ver Listado y Expediente de Clientes',
                'module' => 'clientes',
                'type' => 'menu_access',
                'description' => 'Permite ver el directorio de clientes, expedientes médicos, asistencias y credenciales digitales.',
            ],
            [
                'code' => 'clientes.create',
                'name' => 'Registrar y Editar Clientes',
                'module' => 'clientes',
                'type' => 'action',
                'description' => 'Permite registrar nuevos socios, consultar CNE y actualizar sus datos personales.',
            ],
            [
                'code' => 'clientes.assign_trainer',
                'name' => 'Asignar Entrenador Personal',
                'module' => 'clientes',
                'type' => 'action',
                'description' => 'Permite vincular o desvincular entrenadores personales a los socios.',
            ],
            // Asistencia
            [
                'code' => 'asistencia.view',
                'name' => 'Ver Historial de Asistencia y Aforo',
                'module' => 'asistencia',
                'type' => 'menu_access',
                'description' => 'Permite visualizar la pantalla de torniquete, aforo en tiempo real y logs de acceso.',
            ],
            [
                'code' => 'asistencia.check_in_out',
                'name' => 'Marcar Check-in y Check-out Manual',
                'module' => 'asistencia',
                'type' => 'action',
                'description' => 'Permite buscar por DNI o carnet y validar accesos de entrada y salida manualmente.',
            ],
            // Tienda & POS
            [
                'code' => 'tienda.pos_access',
                'name' => 'Acceso a Punto de Venta (POS)',
                'module' => 'tienda',
                'type' => 'menu_access',
                'description' => 'Permite operar el terminal de ventas para productos, suplementos y cobros rápidos.',
            ],
            [
                'code' => 'tienda.pos_apply_discount',
                'name' => 'Aplicar Descuentos en el POS',
                'module' => 'tienda',
                'type' => 'action',
                'description' => 'Permite aplicar rebajas, códigos promocionales y descuentos directos en el carrito de venta.',
            ],
            [
                'code' => 'tienda.products_view',
                'name' => 'Ver Catálogo e Inventario de Productos',
                'module' => 'tienda',
                'type' => 'menu_access',
                'description' => 'Permite listar los productos en existencia, categorías y alertas de stock bajo.',
            ],
            [
                'code' => 'tienda.products_manage',
                'name' => 'Crear, Editar y Eliminar Productos',
                'module' => 'tienda',
                'type' => 'action',
                'description' => 'Permite la gestión completa del catálogo de productos y códigos de barra.',
            ],
            [
                'code' => 'tienda.products_cost_view',
                'name' => 'Ver Costo de Compra y Margen de Ganancia',
                'module' => 'tienda',
                'type' => 'widget',
                'description' => 'Muestra columnas y métricas confidenciales de costo unitario y margen de utilidad.',
            ],
            [
                'code' => 'tienda.stock_adjust',
                'name' => 'Ajustar y Reabastecer Inventario',
                'module' => 'tienda',
                'type' => 'action',
                'description' => 'Permite ingresar stock por compras o registrar mermas y ajustes de inventario.',
            ],
            [
                'code' => 'tienda.stock_movements_view',
                'name' => 'Ver Historial de Movimientos de Stock (Kardex)',
                'module' => 'tienda',
                'type' => 'menu_access',
                'description' => 'Permite auditar las entradas, salidas y ajustes de almacén.',
            ],
            [
                'code' => 'tienda.sales_history_view',
                'name' => 'Ver Historial de Ventas de la Tienda',
                'module' => 'tienda',
                'type' => 'menu_access',
                'description' => 'Permite ver los tickets de venta generados, cajeros y comprobantes emitidos.',
            ],
            // Finanzas & Membresías
            [
                'code' => 'finanzas.view',
                'name' => 'Ver Módulo Financiero y Métricas',
                'module' => 'finanzas',
                'type' => 'menu_access',
                'description' => 'Permite ver el panel de recaudación, ingresos en USD y Bs., y resumen contable.',
            ],
            [
                'code' => 'finanzas.plans_manage',
                'name' => 'Gestionar Planes de Membresía y Promociones',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite crear y editar tarifas, planes de membresía, cupones y paquetes especiales.',
            ],
            [
                'code' => 'finanzas.record_payment',
                'name' => 'Registrar Pagos, Abonos y Renovaciones',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite cobrar cuotas, registrar abonos a saldo a favor y renovar membresías.',
            ],
            [
                'code' => 'finanzas.approve_payments',
                'name' => 'Aprobar / Rechazar Pagos Reportados',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite verificar transferencias y pagos móviles reportados por los clientes en la app.',
            ],
            [
                'code' => 'finanzas.gateways_manage',
                'name' => 'Configurar Cuentas y Pasarelas de Pago',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite dar de alta cuentas bancarias, Pago Móvil, Zelle y métodos de cobro.',
            ],
            [
                'code' => 'finanzas.exchange_rate_manage',
                'name' => 'Gestionar y Sincronizar Tasa de Cambio (BCV)',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite forzar la sincronización automática con el Banco Central o fijar la tasa manual.',
            ],
            [
                'code' => 'finanzas.export_reports',
                'name' => 'Exportar Reportes Financieros a Excel',
                'module' => 'finanzas',
                'type' => 'action',
                'description' => 'Permite descargar hojas de cálculo con el detalle de pagos y balances contables.',
            ],
            // Cierre de Caja
            [
                'code' => 'cierre_caja.view',
                'name' => 'Ver Balance Diario y Cierres de Caja',
                'module' => 'cierre_caja',
                'type' => 'menu_access',
                'description' => 'Permite inspeccionar el flujo de ingresos en efectivo, transferencias y POS del día.',
            ],
            [
                'code' => 'cierre_caja.close_day',
                'name' => 'Realizar Arqueo y Cierre Formal del Día',
                'module' => 'cierre_caja',
                'type' => 'action',
                'description' => 'Permite efectuar el corte oficial de caja, cuadre de efectivo y descarga del acta en PDF.',
            ],
            // Clases Grupales
            [
                'code' => 'clases.manage',
                'name' => 'Gestionar Clases Grupales y Horarios',
                'module' => 'clases',
                'type' => 'menu_access',
                'description' => 'Permite crear clases (Spinning, Yoga, CrossFit), definir instructores, aforos y cronogramas.',
            ],
            // Rutinas & Ejercicios
            [
                'code' => 'rutinas.view',
                'name' => 'Ver Rutinas de Entrenamiento y Ejercicios',
                'module' => 'rutinas',
                'type' => 'menu_access',
                'description' => 'Permite consultar la biblioteca de rutinas, series, repeticiones y videos demostrativos.',
            ],
            [
                'code' => 'rutinas.manage',
                'name' => 'Crear, Editar y Asignar Rutinas',
                'module' => 'rutinas',
                'type' => 'action',
                'description' => 'Permite estructurar planes de entrenamiento y asignarlos directamente a los clientes.',
            ],
            // Nutrición & Dietas
            [
                'code' => 'nutricion.view',
                'name' => 'Ver Planes Nutricionales y Recetas',
                'module' => 'nutricion',
                'type' => 'menu_access',
                'description' => 'Permite explorar la biblioteca de planes de alimentación, macros y recetario.',
            ],
            [
                'code' => 'nutricion.manage',
                'name' => 'Crear, Editar y Asignar Planes de Alimentación',
                'module' => 'nutricion',
                'type' => 'action',
                'description' => 'Permite diseñar menús personalizados, calcular requerimientos calóricos y asignarlos a clientes.',
            ],
            // Retos & Desafíos
            [
                'code' => 'retos.manage',
                'name' => 'Gestionar Retos y Desafíos Fitness',
                'module' => 'retos',
                'type' => 'menu_access',
                'description' => 'Permite publicar retos con metas de XP/Token, registrar participantes y calificar progreso.',
            ],
            // Notificaciones Masivas
            [
                'code' => 'notificaciones.send',
                'name' => 'Enviar Notificaciones Push y Alertas',
                'module' => 'notificaciones',
                'type' => 'menu_access',
                'description' => 'Permite redactar y disparar avisos a clientes por gimnasio o segmento de usuarios.',
            ],
            // Staff & Entrenadores
            [
                'code' => 'staff.view',
                'name' => 'Ver Directorio de Staff y Entrenadores',
                'module' => 'staff',
                'type' => 'menu_access',
                'description' => 'Permite listar los instructores y trabajadores asignados al gimnasio.',
            ],
            [
                'code' => 'staff.manage',
                'name' => 'Gestionar Contratación y Ficha del Staff',
                'module' => 'staff',
                'type' => 'action',
                'description' => 'Permite registrar nuevos entrenadores, editar perfiles y gestionar su estado activo/inactivo.',
            ],
            // Cajeros
            [
                'code' => 'cajeros.view',
                'name' => 'Ver Listado de Cajeros y Puntos de Cobro',
                'module' => 'cajeros',
                'type' => 'menu_access',
                'description' => 'Permite supervisar los usuarios con funciones de cajero y sus cajas asignadas.',
            ],
            [
                'code' => 'cajeros.manage',
                'name' => 'Crear, Editar y Asignar Cajas a Cajeros',
                'module' => 'cajeros',
                'type' => 'action',
                'description' => 'Permite vincular usuarios al rol cajero, asignar número de caja y modificar sus accesos.',
            ],
            // Catálogos
            [
                'code' => 'catalogos.manage',
                'name' => 'Gestionar Catálogos Base (Equipos, Ingredientes, Ejercicios)',
                'module' => 'catalogos',
                'type' => 'action',
                'description' => 'Permite administrar las categorías maestras, máquinas de gimnasio y materias primas.',
            ],
            // Permisos & Seguridad
            [
                'code' => 'permisos.manage',
                'name' => 'Administración de Roles, Permisos y Seguridad (RBAC)',
                'module' => 'permisos',
                'type' => 'menu_access',
                'description' => 'Control total sobre la matriz de permisos por rol y las excepciones personalizadas de usuario.',
            ],
        ];

        // Insert or update permissions
        foreach ($catalog as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $perm['code']],
                [
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'type' => $perm['type'],
                    'description' => $perm['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Retrieve mapped IDs
        $permMap = DB::table('permissions')->pluck('id', 'code')->toArray();

        // 1. Admin Role (All Permissions)
        $adminPermIds = array_values($permMap);
        foreach ($adminPermIds as $pId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role' => 'admin', 'permission_id' => $pId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // 2. Cajero Role
        $cajeroCodes = [
            'dashboard.view',
            'clientes.view',
            'clientes.create',
            'asistencia.view',
            'asistencia.check_in_out',
            'tienda.pos_access',
            'tienda.products_view',
            'tienda.sales_history_view',
            'finanzas.view',
            'finanzas.record_payment',
            'cierre_caja.view',
            'cierre_caja.close_day',
        ];
        foreach ($cajeroCodes as $code) {
            if (isset($permMap[$code])) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role' => 'cajero', 'permission_id' => $permMap[$code]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        // 3. Trainer Role
        $trainerCodes = [
            'dashboard.view',
            'clientes.view',
            'asistencia.view',
            'rutinas.view',
            'rutinas.manage',
            'nutricion.view',
            'nutricion.manage',
            'clases.manage',
            'retos.manage',
            'catalogos.manage',
        ];
        foreach ($trainerCodes as $code) {
            if (isset($permMap[$code])) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role' => 'trainer', 'permission_id' => $permMap[$code]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
};
