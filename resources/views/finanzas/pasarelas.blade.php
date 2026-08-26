@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 shadow-xl backdrop-blur-md">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-lime-500/20 to-emerald-500/20 border border-lime-500/30 rounded-2xl text-lime-400">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-100 tracking-tight flex items-center gap-2">
                        Pasarelas de Pago & API para APP
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Configura los métodos de cobro en línea y presenciales para <strong class="text-lime-400 font-semibold">{{ $activeGym->name ?? 'Tu Gimnasio' }}</strong>.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- API REST Endpoint Pill -->
            <button onclick="openApiTesterModal()" class="px-3.5 py-2 bg-slate-950 hover:bg-slate-850 border border-slate-800 text-slate-300 rounded-xl text-xs font-mono font-bold flex items-center gap-2 transition-all shadow-sm group">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <i data-lucide="code-2" class="w-4 h-4 text-emerald-400 group-hover:rotate-12 transition-transform"></i>
                <span>API REST para APP</span>
            </button>

            <!-- New Gateway Button -->
            @if(auth()->user()->hasPermission('finanzas.gateways_manage'))
                <button onclick="openNewGatewayModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Configurar Nueva Pasarela</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Quick Setup Presets Bar -->
    <div class="space-y-3">
        <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-2">
            <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i> Plantillas de Métodos Rápidos
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7 gap-3">
            @foreach($providerTemplates as $key => $tmpl)
                <button type="button" onclick="openNewGatewayModal('{{ $key }}')" class="p-3 bg-slate-900/40 hover:bg-slate-850/80 border border-slate-800/80 hover:border-slate-750 rounded-2xl text-left transition-all group flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="p-2 rounded-xl {{ $tmpl['color'] }}">
                            <i data-lucide="{{ $tmpl['icon'] }}" class="w-4 h-4"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-600 group-hover:text-lime-400 transition-colors"></i>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-200 group-hover:text-lime-400 transition-colors leading-tight">{{ $tmpl['name'] }}</span>
                        <span class="block text-[9px] text-slate-500 mt-0.5 font-semibold">{{ $tmpl['badge'] }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Active Gateways List Section -->
    <div class="bg-slate-900/60 rounded-3xl border border-slate-800/80 shadow-xl overflow-hidden backdrop-blur-md">
        <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
            <h2 id="gateways-count-header" class="text-sm font-extrabold uppercase tracking-wider text-slate-100 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-lime-400"></i> Pasarelas Habilitadas ({{ count($gateways) }})
            </h2>
            <span class="text-xs text-slate-500 font-medium">
                Orden de aparición en el checkout del cliente
            </span>
        </div>

        <!-- Empty State Container -->
        <div id="gateways-empty-state" class="py-16 px-6 text-center space-y-4 {{ count($gateways) === 0 ? '' : 'hidden' }}">
            <div class="w-16 h-16 mx-auto bg-slate-950 rounded-full border border-slate-800 flex items-center justify-center text-slate-600">
                <i data-lucide="qr-code" class="w-8 h-8"></i>
            </div>
            <div class="max-w-md mx-auto space-y-1">
                <h3 class="font-bold text-slate-200 text-sm">No hay pasarelas de pago configuradas</h3>
                <p class="text-xs text-slate-400">Agrega tu primer método de pago (Pago Móvil, Zelle, Stripe, PayPal, Binance o Efectivo) para habilitar el cobro a través de la aplicación móvil.</p>
            </div>
            <button onclick="openNewGatewayModal()" class="px-4 py-2 bg-lime-500/10 hover:bg-lime-500/20 text-lime-400 border border-lime-500/30 rounded-xl text-xs font-bold transition-all">
                + Configurar Primera Pasarela
            </button>
        </div>

        <!-- Table Container -->
        <div id="gateways-table-container" class="overflow-x-auto {{ count($gateways) > 0 ? '' : 'hidden' }}">
            <table class="w-full text-left text-xs min-w-[750px] whitespace-nowrap">
                <thead class="bg-slate-950/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="py-3.5 px-6">Pasarela / Método</th>
                        <th class="py-3.5 px-4">Entorno</th>
                        <th class="py-3.5 px-4">Correo de Contacto</th>
                        <th class="py-3.5 px-4">Comisión / Mínimo</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="gateways-tbody" class="divide-y divide-slate-800/60 font-medium">
                    @foreach($gateways as $gw)
                        @php
                            $tmpl = $providerTemplates[$gw->provider] ?? [
                                'name' => ucfirst($gw->provider),
                                'badge' => 'Personalizado',
                                'color' => 'bg-slate-800 text-slate-300 border-slate-700',
                                'icon' => 'credit-card',
                            ];
                            $extra = $gw->extra_config ?? [];
                            $emailVal = null;
                            $qrImg = $gw->credentials['qr_code_image'] ?? null;
                            if (is_array($gw->credentials)) {
                                foreach (['account_email', 'merchant_email', 'email'] as $eKey) {
                                    if (!empty($gw->credentials[$eKey])) {
                                        $emailVal = $gw->credentials[$eKey];
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <tr id="gw_row_{{ $gw->id }}" class="hover:bg-slate-850/40 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl border shrink-0 {{ $tmpl['color'] }}">
                                        <i data-lucide="{{ $tmpl['icon'] }}" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-slate-100 text-sm group-hover:text-lime-400 transition-colors truncate">{{ $gw->title }}</h3>
                                            @if($qrImg)
                                                <button type="button" onclick="openViewQrModal('{{ $qrImg }}', '{{ addslashes($gw->title) }}')" class="p-1 bg-slate-950 border border-slate-800 hover:border-lime-500/50 rounded-lg transition-all" title="Ver Código QR">
                                                    <img src="{{ $qrImg }}" alt="QR" class="w-4 h-4 object-cover rounded">
                                                </button>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-1.5 py-0.5 bg-slate-950 border border-slate-800 text-[9px] font-mono font-bold text-slate-400 rounded uppercase">
                                                {{ $gw->provider }}
                                            </span>
                                            @if($gw->description)
                                                <span class="text-[10px] text-slate-500 truncate max-w-[220px]">{{ $gw->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($gw->environment === 'production')
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        ● Producción (Live)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        ⚡ Pruebas (Sandbox)
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($emailVal)
                                    <span class="text-slate-300 font-semibold text-xs flex items-center gap-1.5">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-lime-400"></i>
                                        {{ $emailVal }}
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[10px] italic">Sin Correo</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="space-y-0.5">
                                    <span class="block text-[11px] font-bold text-slate-300">
                                        Comisión: <span class="text-lime-400">{{ number_format($extra['fee_percent'] ?? 0, 1) }}%</span>
                                    </span>
                                    <span class="block text-[10px] text-slate-500">
                                        Mínimo: ${{ number_format($extra['min_amount'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </td>

                            <!-- ESTADO (Read-Only Info Cell) -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <span id="status_badge_{{ $gw->id }}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider {{ $gw->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                    {{ $gw->is_active ? 'Activa' : 'Inhabilitada' }}
                                </span>
                            </td>

                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if(auth()->user()->hasPermission('finanzas.gateways_manage'))
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" onclick='openEditGatewayModal(@json($gw))' id="edit_btn_{{ $gw->id }}" class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Pasarela">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Toggle Active / Inhabilitar Status Action Button -->
                                        <button type="button" onclick="openToggleGatewayModal({{ $gw->id }}, '{{ addslashes($gw->title) }}', {{ $gw->is_active ? 1 : 0 }})" 
                                                id="toggle_btn_{{ $gw->id }}" 
                                                class="p-2 {{ $gw->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                                title="{{ $gw->is_active ? 'Inhabilitar Pasarela' : 'Habilitar Pasarela' }}">
                                            <i data-lucide="{{ $gw->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: CONFIGURAR / EDITAR PASARELA ================= -->
<div id="modal-gateway-config" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-2xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 id="modal-gateway-title" class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="qr-code" class="w-4 h-4 text-lime-400"></i> Configurar Pasarela de Pago
            </h3>
            <button type="button" onclick="toggleModal('modal-gateway-config')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="gateway-config-form" action="{{ route('pasarelas.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitGatewayForm(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="_method" id="gateway-form-method" value="POST">
            <input type="hidden" name="gateway_id" id="gateway_id" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="provider_select" class="block text-slate-400 uppercase tracking-wider mb-1.5">Proveedor / Tipo de Pasarela *</label>
                    <select name="provider" id="provider_select" onchange="onProviderChange(this.value)" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="pago_movil">Pago Móvil (Venezuela)</option>
                        <option value="zelle">Zelle (USD)</option>
                        <option value="stripe">Stripe (Tarjetas de Crédito / Débito)</option>
                        <option value="paypal">PayPal Express Checkout</option>
                        <option value="mercadopago">MercadoPago</option>
                        <option value="binance">Binance Pay (USDT / Cripto)</option>
                        <option value="cash">Efectivo / Caja Recepción</option>
                    </select>
                </div>

                <div>
                    <label for="environment_select" class="block text-slate-400 uppercase tracking-wider mb-1.5">Entorno de Operación *</label>
                    <select name="environment" id="environment_select" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="production">Producción (Live Real)</option>
                        <option value="sandbox">Pruebas (Sandbox / Test)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="gw_title" class="block text-slate-400 uppercase tracking-wider mb-1.5">Título Visible para Cliente *</label>
                    <input type="text" name="title" id="gw_title" required placeholder="Ej: Pago Móvil Banesco" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="gw_sort_order" class="block text-slate-400 uppercase tracking-wider mb-1.5">Orden de Aparición (Sort Order)</label>
                    <input type="number" name="sort_order" id="gw_sort_order" value="0" min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="gw_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción / Instrucciones Previas</label>
                <textarea name="description" id="gw_description" rows="2" placeholder="Instrucciones breves mostradas al usuario antes de pagar..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <!-- Dynamic Provider Specific Fields Box -->
            <div class="p-4 bg-slate-950/80 border border-slate-850 rounded-2xl space-y-3">
                <h4 class="text-[11px] font-extrabold uppercase tracking-widest text-lime-400 flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i> Credenciales & Datos del Método
                </h4>
                <div id="dynamic-credentials-container" class="grid grid-cols-1 gap-3">
                    <!-- Dynamic inputs injected by JS -->
                </div>

                <!-- QR Code Image Upload Box -->
                <div class="pt-3 border-t border-slate-850">
                    <label for="qr_code_file" class="block text-slate-400 text-[10px] uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="qr-code" class="w-3.5 h-3.5 text-lime-400"></i> Imagen de Código QR para Pago (Opcional)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="file" name="qr_code_file" id="qr_code_file" accept="image/*" onchange="previewQrCodeSelected(this)" class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-850 file:text-slate-200 hover:file:bg-slate-800 cursor-pointer">
                        <div id="qr_preview_container" class="hidden shrink-0">
                            <img id="qr_preview_img" src="" alt="Vista previa QR" class="w-12 h-12 object-cover rounded-xl border border-slate-750 bg-slate-950 p-1 shadow">
                        </div>
                    </div>
                    <p class="text-[9px] text-slate-500 mt-1">Formato PNG, JPG, WEBP. Guardado en <code class="text-slate-400 font-mono">public/uploads/payment</code> para la APP Móvil.</p>
                </div>
            </div>

            <!-- Commission & Extra Config -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="gw_fee_percent" class="block text-slate-400 uppercase tracking-wider mb-1.5">Comisión Adicional (%)</label>
                    <input type="number" step="0.1" name="fee_percent" id="gw_fee_percent" value="0" min="0" max="100" placeholder="0.0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="gw_min_amount" class="block text-slate-400 uppercase tracking-wider mb-1.5">Monto Mínimo Permitido ($)</label>
                    <input type="number" step="0.01" name="min_amount" id="gw_min_amount" value="0" min="0" placeholder="0.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="gw_instructions_post_payment" class="block text-slate-400 uppercase tracking-wider mb-1.5">Instrucciones Post-Pago (Mensaje de Confirmación)</label>
                <textarea name="instructions_post_payment" id="gw_instructions_post_payment" rows="2" placeholder="Instrucciones que vera el usuario despues de procesar o enviar el pago..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="gw_is_active" value="1" checked class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-lime-500 focus:ring-lime-500/20">
                <label for="gw_is_active" class="text-xs font-bold text-slate-200 cursor-pointer">Habilitar esta pasarela inmediatamente para cobros</label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-gateway-config')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="gateway-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Pasarela</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO (HABILITAR / INHABILITAR) ================= -->
<div id="modal-toggle-gateway" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="toggle-modal-icon-bg" class="p-2.5 rounded-2xl border bg-rose-500/10 text-rose-400 border-rose-500/20">
                    <i id="toggle-modal-icon" data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="toggle-modal-title" class="font-extrabold text-sm text-slate-100 uppercase tracking-widest">
                        Inhabilitar Pasarela
                    </h3>
                    <p class="text-[10px] text-slate-400">Confirmación de cambio de estado</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-toggle-gateway')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <p id="toggle-modal-desc" class="text-xs text-slate-300 leading-relaxed">
            ¿Estás seguro de cambiar el estado de la pasarela de pago?
        </p>

        <form id="toggle-gateway-form" onsubmit="submitToggleGateway(event)" class="flex items-center justify-end gap-3 pt-2">
            <input type="hidden" id="toggle_gateway_id" value="">
            <button type="button" onclick="toggleModal('modal-toggle-gateway')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 text-xs font-semibold rounded-xl transition-all">
                Cancelar
            </button>
            <button type="submit" id="toggle-gateway-submit-btn" class="px-5 py-2.5 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                Confirmar
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: VER CÓDIGO QR EN GRANDE ================= -->
<div id="modal-view-qr" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-sm mx-auto my-auto space-y-4 animate-scale-up shadow-2xl text-center">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 id="qr-view-title" class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="qr-code" class="w-4 h-4 text-lime-400"></i> Código QR de Pago
            </h3>
            <button type="button" onclick="toggleModal('modal-view-qr')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-3 bg-slate-950 rounded-2xl border border-slate-850 inline-block mx-auto shadow-inner">
            <img id="qr-view-img" src="" alt="Código QR" class="max-w-full max-h-64 object-contain rounded-xl mx-auto">
        </div>

        <div class="flex items-center justify-center gap-2 pt-2">
            <a id="qr-download-link" href="" target="_blank" download class="px-4 py-2 bg-slate-850 hover:bg-slate-800 text-slate-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Abrir en Pestaña
            </a>
            <button type="button" onclick="toggleModal('modal-view-qr')" class="px-4 py-2 bg-lime-500/10 hover:bg-lime-500/20 text-lime-400 border border-lime-500/30 rounded-xl text-xs font-bold transition-all">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL: API REST TESTER FOR MOBILE APP ================= -->
<div id="modal-api-tester" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="code-2" class="w-4 h-4 text-emerald-400"></i> Respuesta API REST para la APP Móvil
            </h3>
            <button type="button" onclick="toggleModal('modal-api-tester')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-xs font-mono">
            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 font-bold rounded uppercase text-[10px]">GET</span>
                    <span class="text-slate-300 ml-2 text-[11px]">/api/v1/gyms/{{ $activeGym->id }}/payment-gateways</span>
                </div>
                <button onclick="copyApiUrl('/api/v1/gyms/{{ $activeGym->id }}/payment-gateways')" class="px-2.5 py-1 bg-slate-850 hover:bg-slate-800 text-slate-300 rounded text-[10px] font-sans font-bold">Copiar URL</button>
            </div>

            <div>
                <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1 font-sans">Estructura JSON entregada a la APP:</span>
                <pre id="api-json-preview" class="p-4 bg-slate-950 rounded-2xl border border-slate-850 text-lime-400 text-[11px] leading-relaxed max-h-80 overflow-y-auto">Cargando respuesta API...</pre>
            </div>
            <p class="text-[11px] font-sans text-slate-500 italic">
                * Nota de seguridad: Todas las claves secretas privadas (secret keys) son automáticamente sanitizadas y excluidas del payload enviado a dispositivos móviles.
            </p>
        </div>
    </div>
</div>

<script>
    const providerTemplates = @json($providerTemplates);

    function extractGatewayEmail(gw) {
        const creds = gw.credentials || {};
        return creds.account_email || creds.merchant_email || creds.email || null;
    }

    function renderGatewayRowHTML(gw) {
        const tmpl = providerTemplates[gw.provider] || {
            name: gw.provider,
            badge: 'Personalizado',
            color: 'bg-slate-800 text-slate-300 border-slate-700',
            icon: 'credit-card'
        };
        const extra = gw.extra_config || {};
        const email = extractGatewayEmail(gw);
        const qrImg = gw.credentials ? gw.credentials.qr_code_image : null;
        const feePct = (parseFloat(extra.fee_percent) || 0).toFixed(1);
        const minAmt = (parseFloat(extra.min_amount) || 0).toFixed(2);
        const gwJsonStr = JSON.stringify(gw).replace(/'/g, "&#39;");

        const envBadge = gw.environment === 'production'
            ? `<span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">● Producción (Live)</span>`
            : `<span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">⚡ Pruebas (Sandbox)</span>`;

        const statusBadgeClass = gw.is_active 
            ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/20"
            : "bg-rose-500/10 text-rose-400 border-rose-500/20";
        const statusBadgeText = gw.is_active ? "Activa" : "Inhabilitada";

        const toggleBtnClass = gw.is_active
            ? "p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm"
            : "p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm";
        const toggleBtnTitle = gw.is_active ? "Inhabilitar Pasarela" : "Habilitar Pasarela";
        const toggleBtnIcon = gw.is_active ? "power" : "check-circle";

        const emailHTML = email 
            ? `<span class="text-slate-300 font-semibold text-xs flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5 text-lime-400"></i> ${escapeHtml(email)}</span>`
            : `<span class="text-slate-500 text-[10px] italic">Sin Correo</span>`;

        const qrBtnHTML = qrImg 
            ? `<button type="button" onclick="openViewQrModal('${qrImg}', '${escapeHtml(gw.title.replace(/'/g, "\\'"))}')" class="p-1 bg-slate-950 border border-slate-800 hover:border-lime-500/50 rounded-lg transition-all" title="Ver Código QR"><img src="${qrImg}" alt="QR" class="w-4 h-4 object-cover rounded"></button>`
            : '';

        return `
            <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl border shrink-0 ${tmpl.color}">
                        <i data-lucide="${tmpl.icon}" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-100 text-sm group-hover:text-lime-400 transition-colors truncate">${escapeHtml(gw.title)}</h3>
                            ${qrBtnHTML}
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="px-1.5 py-0.5 bg-slate-950 border border-slate-800 text-[9px] font-mono font-bold text-slate-400 rounded uppercase">
                                ${escapeHtml(gw.provider)}
                            </span>
                            ${gw.description ? `<span class="text-[10px] text-slate-500 truncate max-w-[220px]">${escapeHtml(gw.description)}</span>` : ''}
                        </div>
                    </div>
                </div>
            </td>
            <td class="py-4 px-4 whitespace-nowrap">
                ${envBadge}
            </td>
            <td class="py-4 px-4 whitespace-nowrap">
                ${emailHTML}
            </td>
            <td class="py-4 px-4 whitespace-nowrap">
                <div class="space-y-0.5">
                    <span class="block text-[11px] font-bold text-slate-300">
                        Comisión: <span class="text-lime-400">${feePct}%</span>
                    </span>
                    <span class="block text-[10px] text-slate-500">
                        Mínimo: $${minAmt}
                    </span>
                </div>
            </td>
            <td class="py-4 px-4 text-center whitespace-nowrap">
                <span id="status_badge_${gw.id}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider ${statusBadgeClass}">
                    ${statusBadgeText}
                </span>
            </td>
            <td class="py-4 px-6 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                    <button type="button" onclick='openEditGatewayModal(${gwJsonStr})' id="edit_btn_${gw.id}" class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Pasarela">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button type="button" onclick="openToggleGatewayModal(${gw.id}, '${escapeHtml(gw.title.replace(/'/g, "\\'"))}', ${gw.is_active ? 1 : 0})" id="toggle_btn_${gw.id}" class="${toggleBtnClass}" title="${toggleBtnTitle}">
                        <i data-lucide="${toggleBtnIcon}" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
        `;
    }

    function updateGatewaysCount() {
        const tbody = document.getElementById('gateways-tbody');
        const countHeader = document.getElementById('gateways-count-header');
        if (tbody && countHeader) {
            countHeader.innerHTML = `<i data-lucide="credit-card" class="w-4 h-4 text-lime-400"></i> Pasarelas Habilitadas (${tbody.children.length})`;
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function previewQrCodeSelected(input) {
        const container = document.getElementById('qr_preview_container');
        const img = document.getElementById('qr_preview_img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (img) img.src = e.target.result;
                if (container) container.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openViewQrModal(imgUrl, title) {
        document.getElementById('qr-view-title').innerHTML = `<i data-lucide="qr-code" class="w-4 h-4 text-lime-400"></i> QR - ${escapeHtml(title)}`;
        document.getElementById('qr-view-img').src = imgUrl;
        document.getElementById('qr-download-link').href = imgUrl;
        if (window.lucide) window.lucide.createIcons();
        toggleModal('modal-view-qr');
    }



    function onProviderChange(providerKey, existingCredentials = {}) {
        const container = document.getElementById('dynamic-credentials-container');
        if (!container) return;

        const template = providerTemplates[providerKey] || { fields: [] };
        let html = '';

        if (!template.fields || template.fields.length === 0) {
            html = '<p class="text-slate-500 text-xs italic">No se requieren credenciales especiales para esta pasarela.</p>';
        } else {
            template.fields.forEach(field => {
                const val = existingCredentials[field.key] || '';
                html += `
                    <div>
                        <label for="cred_${field.key}" class="block text-slate-400 text-[10px] uppercase tracking-wider mb-1">${field.label}</label>
                        <input type="${field.type}" name="credentials[${field.key}]" id="cred_${field.key}" value="${escapeHtml(val)}" placeholder="${field.placeholder || ''}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-xs focus:outline-none focus:border-lime-500/50">
                    </div>
                `;
            });
        }

        container.innerHTML = html;

        // Auto-fill default title & description if creating new
        const formMethod = document.getElementById('gateway-form-method').value;
        if (formMethod === 'POST') {
            const titleInput = document.getElementById('gw_title');
            const descInput = document.getElementById('gw_description');
            if (titleInput && (!titleInput.value || Object.keys(providerTemplates).some(k => providerTemplates[k].name === titleInput.value))) {
                titleInput.value = template.name || '';
            }
            if (descInput && !descInput.value) {
                descInput.value = template.description || '';
            }
        }
    }

    function openNewGatewayModal(providerKey = 'pago_movil') {
        document.getElementById('gateway-config-form').reset();
        document.getElementById('gateway-form-method').value = 'POST';
        document.getElementById('gateway-config-form').action = "{{ route('pasarelas.store') }}";
        document.getElementById('gateway_id').value = '';
        document.getElementById('modal-gateway-title').innerHTML = '<i data-lucide="plus-circle" class="w-4 h-4 text-lime-400"></i> Configurar Nueva Pasarela';
        
        const provSelect = document.getElementById('provider_select');
        if (provSelect) {
            provSelect.value = providerKey;
            provSelect.disabled = false;
        }

        const qrContainer = document.getElementById('qr_preview_container');
        const qrFileInput = document.getElementById('qr_code_file');
        if (qrFileInput) qrFileInput.value = '';
        if (qrContainer) qrContainer.classList.add('hidden');

        onProviderChange(providerKey);
        toggleModal('modal-gateway-config');
        if (window.lucide) window.lucide.createIcons();
    }

    function openEditGatewayModal(gw) {
        document.getElementById('gateway-config-form').reset();
        document.getElementById('gateway-form-method').value = 'PUT';
        document.getElementById('gateway-config-form').action = `/finanzas/pasarelas/${gw.id}`;
        document.getElementById('gateway_id').value = gw.id;
        document.getElementById('modal-gateway-title').innerHTML = '<i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Pasarela de Pago';

        const provSelect = document.getElementById('provider_select');
        if (provSelect) {
            provSelect.value = gw.provider;
            provSelect.disabled = true; // Cannot change provider type when editing
        }

        document.getElementById('environment_select').value = gw.environment || 'production';
        document.getElementById('gw_title').value = gw.title || '';
        document.getElementById('gw_sort_order').value = gw.sort_order || 0;
        document.getElementById('gw_description').value = gw.description || '';

        const extra = gw.extra_config || {};
        document.getElementById('gw_fee_percent').value = extra.fee_percent || 0;
        document.getElementById('gw_min_amount').value = extra.min_amount || 0;
        document.getElementById('gw_instructions_post_payment').value = extra.instructions_post_payment || '';

        document.getElementById('gw_is_active').checked = !!gw.is_active;

        const qrContainer = document.getElementById('qr_preview_container');
        const qrImg = document.getElementById('qr_preview_img');
        const qrFileInput = document.getElementById('qr_code_file');
        if (qrFileInput) qrFileInput.value = '';

        if (gw.credentials && gw.credentials.qr_code_image) {
            if (qrImg) qrImg.src = gw.credentials.qr_code_image;
            if (qrContainer) qrContainer.classList.remove('hidden');
        } else {
            if (qrContainer) qrContainer.classList.add('hidden');
        }

        onProviderChange(gw.provider, gw.credentials || {});
        toggleModal('modal-gateway-config');
        if (window.lucide) window.lucide.createIcons();
    }

    async function submitGatewayForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('gateway-submit-btn');

        setBtnLoading(submitBtn, true, 'Guardando...');

        try {
            const formData = new FormData(form);
            const provSelect = document.getElementById('provider_select');
            if (provSelect && provSelect.disabled) {
                formData.append('provider', provSelect.value);
            }

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const gw = data.gateway;
                const existingRow = document.getElementById(`gw_row_${gw.id}`);
                const emptyContainer = document.getElementById('gateways-empty-state');
                const tableContainer = document.getElementById('gateways-table-container');
                const tbody = document.getElementById('gateways-tbody');

                if (existingRow) {
                    existingRow.innerHTML = renderGatewayRowHTML(gw);
                } else {
                    if (emptyContainer) emptyContainer.classList.add('hidden');
                    if (tableContainer) tableContainer.classList.remove('hidden');

                    const newTr = document.createElement('tr');
                    newTr.id = `gw_row_${gw.id}`;
                    newTr.className = 'hover:bg-slate-850/40 transition-colors group';
                    newTr.innerHTML = renderGatewayRowHTML(gw);
                    if (tbody) {
                        tbody.appendChild(newTr);
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                updateGatewaysCount();

                toggleModal('modal-gateway-config');
                showToast(data.message, 'success');
            } else {
                let errText = data.message || 'Error al guardar la pasarela.';
                if (data.errors) {
                    errText = Object.values(data.errors).flat().join(' ');
                }
                showToast(errText, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error al conectar con el servidor.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    function openToggleGatewayModal(id, title, isActive) {
        document.getElementById('toggle_gateway_id').value = id;
        const titleEl = document.getElementById('toggle-modal-title');
        const descEl = document.getElementById('toggle-modal-desc');
        const iconBg = document.getElementById('toggle-modal-icon-bg');
        const iconEl = document.getElementById('toggle-modal-icon');
        const submitBtn = document.getElementById('toggle-gateway-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Pasarela';
            descEl.innerHTML = `¿Estás seguro de que deseas inhabilitar la pasarela <strong class="text-slate-100">${escapeHtml(title)}</strong>? Dejará de aparecer en las opciones de cobro para tus clientes.`;
            iconBg.className = "p-2.5 rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/20";
            if (iconEl) iconEl.setAttribute('data-lucide', 'power');
            submitBtn.className = "px-5 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all";
            submitBtn.textContent = "Sí, Inhabilitar";
        } else {
            titleEl.textContent = 'Habilitar Pasarela';
            descEl.innerHTML = `¿Deseas reactivar y habilitar la pasarela <strong class="text-slate-100">${escapeHtml(title)}</strong> para restaurar la opción de pago a tus clientes?`;
            iconBg.className = "p-2.5 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20";
            if (iconEl) iconEl.setAttribute('data-lucide', 'check-circle');
            submitBtn.className = "px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all";
            submitBtn.textContent = "Sí, Habilitar";
        }

        if (window.lucide) window.lucide.createIcons();
        toggleModal('modal-toggle-gateway');
    }

    async function submitToggleGateway(e) {
        e.preventDefault();
        const id = document.getElementById('toggle_gateway_id').value;
        const submitBtn = document.getElementById('toggle-gateway-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/finanzas/pasarelas/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                const badge = document.getElementById(`status_badge_${id}`);
                const toggleBtn = document.getElementById(`toggle_btn_${id}`);
                const gwTitle = data.gateway_title || '';

                if (badge) {
                    if (data.is_active) {
                        badge.className = "px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider bg-emerald-500/10 text-emerald-400 border-emerald-500/20";
                        badge.textContent = "Activa";
                    } else {
                        badge.className = "px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider bg-rose-500/10 text-rose-400 border-rose-500/20";
                        badge.textContent = "Inhabilitada";
                    }
                }

                if (toggleBtn) {
                    const titleText = data.is_active ? 'Inhabilitar Pasarela' : 'Habilitar Pasarela';
                    const iconName = data.is_active ? 'power' : 'check-circle';
                    const btnClass = data.is_active
                        ? "p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm"
                        : "p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm";
                    
                    toggleBtn.className = btnClass;
                    toggleBtn.title = titleText;
                    toggleBtn.innerHTML = `<i data-lucide="${iconName}" class="w-4 h-4"></i>`;
                    toggleBtn.setAttribute('onclick', `openToggleGatewayModal(${id}, '${escapeHtml(gwTitle.replace(/'/g, "\\'"))}', ${data.is_active ? 1 : 0})`);
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-toggle-gateway');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado.', 'error');
            }
        } catch (err) {
            showToast('Error de conexión con el servidor.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function openApiTesterModal() {
        toggleModal('modal-api-tester');
        const jsonPre = document.getElementById('api-json-preview');
        jsonPre.textContent = 'Consultando endpoint /api/v1/gyms/{{ $activeGym->id }}/payment-gateways...';

        try {
            const res = await fetch('/api/v1/gyms/{{ $activeGym->id }}/payment-gateways');
            const data = await res.json();
            jsonPre.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            jsonPre.textContent = 'Error al cargar vista previa API.';
        }
    }

    function copyApiUrl(urlPath) {
        const fullUrl = window.location.origin + urlPath;
        navigator.clipboard.writeText(fullUrl).then(() => {
            showToast('URL de la API copiada al portapapeles.', 'success');
        });
    }

    function setBtnLoading(btn, isLoading, text = 'Cargando...') {
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="flex items-center gap-2 font-bold"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> ${text}</span>`;
            if (window.lucide) window.lucide.createIcons();
        } else {
            btn.disabled = false;
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endsection
