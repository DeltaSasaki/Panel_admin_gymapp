<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GymPaymentGateway;
use App\Models\Gym;
use App\Models\AdminAuditLog;

class PaymentGatewayController extends Controller
{
    /**
     * Resolve current active Gym ID for logged in user or SuperAdmin switcher
     */
    protected function resolveCurrentGymId(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            $sessionGym = session('superadmin_gym_id');
            if ($sessionGym && $sessionGym !== 'all') {
                return (int)$sessionGym;
            }
        }
        return $user->gym_id ? (int)$user->gym_id : 1;
    }

    /**
     * Display Payment Gateways list & configuration screen
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso no autorizado.');
        }

        $activeGymId = $this->resolveCurrentGymId($request);
        $activeGym = Gym::find($activeGymId);

        $gateways = GymPaymentGateway::where('gym_id', $activeGymId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Preset templates metadata for friendly UI setup
        $providerTemplates = [
            'pago_movil' => [
                'name' => 'Pago Móvil (Venezuela)',
                'badge' => 'Transferencia',
                'color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'icon' => 'smartphone',
                'description' => 'Pago móvil bancario instantáneo (Banesco, Mercantil, BBVA Provincial, etc.)',
                'fields' => [
                    ['key' => 'bank_name', 'label' => 'Banco de Destino', 'type' => 'text', 'placeholder' => 'Ej: Banesco (0134)'],
                    ['key' => 'phone', 'label' => 'Teléfono Registrado', 'type' => 'text', 'placeholder' => 'Ej: 0412-1234567'],
                    ['key' => 'dni_rif', 'label' => 'Cédula o RIF del Titular', 'type' => 'text', 'placeholder' => 'Ej: J-123456789 o V-18999888'],
                ]
            ],
            'zelle' => [
                'name' => 'Zelle (USD)',
                'badge' => 'Transferencia USD',
                'color' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                'icon' => 'dollar-sign',
                'description' => 'Transferencias electrónicas Zelle en dólares estadounidenses',
                'fields' => [
                    ['key' => 'account_email', 'label' => 'Correo Registrado en Zelle', 'type' => 'email', 'placeholder' => 'pagos@tugimnasio.com'],
                    ['key' => 'account_holder', 'label' => 'Nombre Completo del Titular', 'type' => 'text', 'placeholder' => 'Ej: Corp Fitness International LLC'],
                ]
            ],
            'stripe' => [
                'name' => 'Stripe (Tarjetas Crédito/Débito)',
                'badge' => 'Pasarela API Directa',
                'color' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                'icon' => 'credit-card',
                'description' => 'Cobros con tarjetas Visa, MasterCard, Amex a través del SDK de Stripe',
                'fields' => [
                    ['key' => 'publishable_key', 'label' => 'Clave Pública (Publishable Key / pk_)', 'type' => 'text', 'placeholder' => 'pk_live_... o pk_test_...'],
                    ['key' => 'secret_key', 'label' => 'Clave Secreta (Secret Key / sk_)', 'type' => 'password', 'placeholder' => 'sk_live_... o sk_test_...'],
                    ['key' => 'webhook_secret', 'label' => 'Secreto de Webhook (Opcional / whsec_)', 'type' => 'password', 'placeholder' => 'whsec_...'],
                ]
            ],
            'paypal' => [
                'name' => 'PayPal Express Checkout',
                'badge' => 'Pasarela API Directa',
                'color' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'icon' => 'globe',
                'description' => 'Pagos globales con saldo PayPal o tarjetas de crédito',
                'fields' => [
                    ['key' => 'client_id', 'label' => 'PayPal Client ID', 'type' => 'text', 'placeholder' => 'Client ID de Developer PayPal'],
                    ['key' => 'secret_key', 'label' => 'PayPal Secret Key', 'type' => 'password', 'placeholder' => 'Secret Key de Developer PayPal'],
                    ['key' => 'merchant_email', 'label' => 'Correo de Cuenta PayPal Business', 'type' => 'email', 'placeholder' => 'ventas@gimnasio.com'],
                ]
            ],
            'mercadopago' => [
                'name' => 'MercadoPago',
                'badge' => 'Pasarela LATAM',
                'color' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                'icon' => 'shopping-bag',
                'description' => 'Pasarela de pagos líder para Latinoamérica (MercadoPago)',
                'fields' => [
                    ['key' => 'public_key', 'label' => 'Public Key (PK)', 'type' => 'text', 'placeholder' => 'APP_USR-...'],
                    ['key' => 'access_token', 'label' => 'Access Token (Token Privado)', 'type' => 'password', 'placeholder' => 'APP_USR-...'],
                ]
            ],
            'binance' => [
                'name' => 'Binance Pay (Cripto / USDT)',
                'badge' => 'Criptomonedas',
                'color' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                'icon' => 'qr-code',
                'description' => 'Cobros automáticos o por código Pay ID en USDT y Criptos',
                'fields' => [
                    ['key' => 'binance_pay_id', 'label' => 'Binance Pay ID / User ID', 'type' => 'text', 'placeholder' => 'Ej: 189876543'],
                    ['key' => 'api_key', 'label' => 'Binance Merchant API Key (Opcional)', 'type' => 'text', 'placeholder' => 'API Key de Binance Merchant'],
                    ['key' => 'secret_key', 'label' => 'Binance Merchant Secret Key (Opcional)', 'type' => 'password', 'placeholder' => 'Secret Key de Binance Merchant'],
                ]
            ],
            'cash' => [
                'name' => 'Efectivo / Caja Recepción',
                'badge' => 'Presencial / Manual',
                'color' => 'bg-lime-500/10 text-lime-400 border-lime-500/20',
                'icon' => 'banknote',
                'description' => 'Pago directo en taquilla o recepción del gimnasio',
                'fields' => [
                    ['key' => 'location_notes', 'label' => 'Ubicación o Taquilla de Cobro', 'type' => 'text', 'placeholder' => 'Ej: Taquilla Principal - Recepción Central'],
                ]
            ],
        ];

        return view('finanzas.pasarelas', compact('gateways', 'activeGym', 'providerTemplates'));
    }

    /**
     * Store a new payment gateway configuration
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $request->validate([
            'provider' => 'required|string|max:50',
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'instructions_post_payment' => 'nullable|string',
            'credentials' => 'nullable|array',
            'qr_code_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $activeGymId = $this->resolveCurrentGymId($request);
        $credentials = $request->credentials ?? [];

        // Handle QR Code Image Upload
        if ($request->hasFile('qr_code_file')) {
            $file = $request->file('qr_code_file');
            $uploadDir = public_path('uploads/payment');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'qr_gym_' . $activeGymId . '_' . strtolower($request->provider) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $credentials['qr_code_image'] = '/uploads/payment/' . $filename;
        }

        $extraConfig = [
            'fee_percent' => (float)($request->fee_percent ?? 0),
            'min_amount' => (float)($request->min_amount ?? 0),
            'instructions_post_payment' => $request->instructions_post_payment ?? '',
        ];

        $gateway = GymPaymentGateway::create([
            'gym_id' => $activeGymId,
            'provider' => strtolower($request->provider),
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'environment' => $request->environment,
            'credentials' => $credentials,
            'extra_config' => $extraConfig,
            'sort_order' => (int)($request->sort_order ?? 0),
        ]);

        AdminAuditLog::logAction(
            'INSERT',
            'gym_payment_gateways',
            $gateway->id,
            null,
            $gateway->toArray(),
            $activeGymId
        );

        $message = "Pasarela de pago '{$gateway->title}' configurada exitosamente.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gateway' => $gateway
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an existing payment gateway configuration
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $gateway = GymPaymentGateway::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
            'sort_order' => 'nullable|integer',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'instructions_post_payment' => 'nullable|string',
            'credentials' => 'nullable|array',
            'qr_code_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $oldData = $gateway->toArray();
        $existingCreds = $gateway->credentials ?? [];
        $newCreds = $request->credentials ?? [];
        
        // Preserve password fields if left empty in form
        foreach ($newCreds as $k => $v) {
            if (empty($v) && isset($existingCreds[$k]) && in_array($k, ['secret_key', 'api_secret', 'access_token', 'webhook_secret'])) {
                $newCreds[$k] = $existingCreds[$k];
            }
        }

        // Preserve existing qr_code_image if no new file uploaded
        if (isset($existingCreds['qr_code_image'])) {
            $newCreds['qr_code_image'] = $existingCreds['qr_code_image'];
        }

        // Handle QR Code Image Upload
        if ($request->hasFile('qr_code_file')) {
            $file = $request->file('qr_code_file');
            $uploadDir = public_path('uploads/payment');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'qr_gym_' . $gateway->gym_id . '_' . strtolower($gateway->provider) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $newCreds['qr_code_image'] = '/uploads/payment/' . $filename;
        }

        $extraConfig = [
            'fee_percent' => (float)($request->fee_percent ?? 0),
            'min_amount' => (float)($request->min_amount ?? 0),
            'instructions_post_payment' => $request->instructions_post_payment ?? '',
        ];

        $gateway->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'environment' => $request->environment,
            'credentials' => $newCreds,
            'extra_config' => $extraConfig,
            'sort_order' => (int)($request->sort_order ?? 0),
        ]);

        AdminAuditLog::logAction(
            'UPDATE',
            'gym_payment_gateways',
            $gateway->id,
            $oldData,
            $gateway->fresh()->toArray(),
            $gateway->gym_id
        );

        $message = "Pasarela '{$gateway->title}' actualizada exitosamente.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gateway' => $gateway
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle gateway active status via AJAX
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $gateway = GymPaymentGateway::findOrFail($id);
        $oldData = ['is_active' => $gateway->is_active];
        $newStatus = $gateway->is_active ? 0 : 1;
        $gateway->update(['is_active' => $newStatus]);

        AdminAuditLog::logAction(
            'UPDATE',
            'gym_payment_gateways',
            $gateway->id,
            $oldData,
            ['is_active' => $newStatus],
            $gateway->gym_id
        );

        $statusLabel = $newStatus ? 'habilitada' : 'inhabilitada';
        $message = "Pasarela '{$gateway->title}' {$statusLabel} con éxito.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'gateway_title' => $gateway->title,
            'is_active' => $newStatus
        ]);
    }

    /**
     * Delete payment gateway configuration
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $gateway = GymPaymentGateway::findOrFail($id);
        $oldData = $gateway->toArray();
        $gymId = $gateway->gym_id;
        $name = $gateway->title;
        $gateway->delete();

        AdminAuditLog::logAction(
            'DELETE',
            'gym_payment_gateways',
            $id,
            $oldData,
            null,
            $gymId
        );

        $message = "Pasarela '{$name}' eliminada correctamente.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * REST API Endpoint for Mobile APP / Web Checkout Clients
     * GET /api/v1/gyms/{gym_id}/payment-gateways
     */
    public function apiGetGymGateways(Request $request, $gym_id)
    {
        $gym = Gym::find($gym_id);
        if (!$gym) {
            return response()->json([
                'success' => false,
                'message' => 'Gimnasio no encontrado.'
            ], 404);
        }

        $gateways = GymPaymentGateway::where('gym_id', $gym_id)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($gw) {
                return [
                    'id' => $gw->id,
                    'provider' => $gw->provider,
                    'title' => $gw->title,
                    'description' => $gw->description,
                    'environment' => $gw->environment,
                    'credentials' => $gw->getPublicSanitizedCredentials(),
                    'extra_config' => $gw->extra_config ?? [],
                    'sort_order' => $gw->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'gym_id' => (int)$gym_id,
            'gym_name' => $gym->name,
            'count' => count($gateways),
            'gateways' => $gateways
        ]);
    }
}
