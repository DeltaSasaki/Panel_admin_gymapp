<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryProduct;
use App\Models\ProductCategory;
use App\Models\ProductSale;
use App\Models\SaleItem;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * View POS sales terminal (accessible by trainers & admins).
     */
    public function pos()
    {
        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->route('dashboard')->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder abrir la terminal de ventas (POS).']);
        }

        // Fetch active products with stock > 0
        $products = InventoryProduct::where('gym_id', $gymId)
            ->where('is_available', 1)
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->get();

        // Fetch clients for dropdown (optional customer association)
        $clients = User::where('role', 'member')->where('gym_id', $gymId)->with('profile')->get();

        return view('tienda.pos', compact('products', 'clients'));
    }

    /**
     * Submit a new sale from the POS terminal.
     */
    public function registerSale(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,transfer,other',
            'user_id' => 'nullable|exists:users,id',
            'cart' => 'required|json', // JSON array of items: [{product_id: 1, quantity: 2}]
            'notes' => 'nullable|string',
            'promo_code' => 'nullable|string',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder realizar una venta.']);
        }

        $cart = json_decode($request->cart, true);

        if (empty($cart)) {
            return redirect()->back()->withErrors(['cart' => 'El carrito está vacío.']);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $itemsToCreate = [];

            foreach ($cart as $item) {
                $product = InventoryProduct::where('gym_id', $gymId)->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: " . $product->name);
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Find and validate promo code if provided
            $promoId = null;
            $discountPercentage = 0;
            $discountFixed = 0;

            if ($request->filled('promo_code')) {
                $promoCodeStr = strtoupper($request->promo_code);
                $promo = \App\Models\PromoCode::where('code', $promoCodeStr)
                    ->where('is_active', 1)
                    ->where(function($q) use ($gymId) {
                        $q->where('gym_id', $gymId)->orWhereNull('gym_id');
                    })
                    ->first();

                if (!$promo) {
                    throw new \Exception("El código promocional '{$promoCodeStr}' no es válido o ya venció.");
                }

                // Date checks
                $now = Carbon::now();
                if ($promo->valid_from && Carbon::parse($promo->valid_from)->isFuture()) {
                    throw new \Exception("El código promocional '{$promoCodeStr}' aún no inicia.");
                }
                if ($promo->valid_until && Carbon::parse($promo->valid_until)->isPast()) {
                    throw new \Exception("El código promocional '{$promoCodeStr}' ha expirado.");
                }

                // Max uses check
                if ($promo->max_uses && $promo->current_uses >= $promo->max_uses) {
                    throw new \Exception("El código promocional '{$promoCodeStr}' ya alcanzó su límite máximo de usos.");
                }

                $promoId = $promo->id;
                if ($promo->discount_type === 'percentage') {
                    $discountPercentage = (float)$promo->discount_value;
                } else {
                    $discountFixed = (float)$promo->discount_value;
                }
            }

            // Calculate discount if promo code applied
            $originalTotal = $totalAmount;
            if ($promoId) {
                if ($discountPercentage > 0) {
                    $totalAmount = $totalAmount * (1 - ($discountPercentage / 100));
                } else {
                    $totalAmount = max(0, $totalAmount - $discountFixed);
                }
                
                // Increment promo code uses
                $promo->increment('current_uses');
            }

            // Create product sale record
            $sale = ProductSale::create([
                'gym_id' => $gymId,
                'user_id' => $request->user_id,
                'promo_code_id' => $promoId,
                'sold_by' => auth()->user()->id,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'sale_date' => Carbon::now(),
                'notes' => $request->notes,
            ]);

            // Save individual items (Fires the database triggers)
            foreach ($itemsToCreate as $item) {
                $item['sale_id'] = $sale->id;
                SaleItem::create($item);
            }

            AdminAuditLog::record('INSERT', 'product_sales', $sale->id, null, $sale->toArray(), $gymId);

            DB::commit();

            $successMsg = 'Venta registrada con éxito. Total cobrado: $' . number_format($totalAmount, 2);
            if ($promoId) {
                $successMsg .= ' (Descuento aplicado, total original: $' . number_format($originalTotal, 2) . ')';
            }

            return redirect()->route('tienda.pos')->with('success', $successMsg);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error al registrar venta: ' . $errorMessage;
            }
            return redirect()->back()->withInput()->withErrors(['cart' => $errorText]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * View Inventory and categories (restristed to Admins).
     */
    public function products()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $productsQuery = InventoryProduct::with('category');
        $categoriesQuery = ProductCategory::query();
        
        if ($gymId !== 'all') {
            $productsQuery->where('gym_id', $gymId);
            $categoriesQuery->where('gym_id', $gymId);
        }

        $products = $productsQuery->get();
        $categories = $categoriesQuery->get();

        return view('tienda.productos', compact('products', 'categories'));
    }

    /**
     * View stock movements history (restricted to Admins).
     */
    public function stockMovements()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $movementsQuery = \App\Models\InventoryMovement::with(['product', 'performer.profile']);
        if ($gymId !== 'all') {
            $movementsQuery->whereHas('product', function($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }
        $movements = $movementsQuery->get();

        // Fetch audit log entries from admin_audit_logs table
        $auditLogsQuery = \App\Models\AdminAuditLog::with(['admin.profile'])
            ->whereIn('table_name', ['inventory_products', 'product_categories', 'inventory_movements', 'product_sales']);

        if ($gymId !== 'all') {
            $auditLogsQuery->where(function($q) use ($gymId) {
                $q->where('gym_id', $gymId)->orWhereNull('gym_id');
            });
        }
        $auditLogs = $auditLogsQuery->get();

        // Combine movements and audit logs into a single unified list sorted DESCENDING by date
        $combinedItems = collect();

        foreach ($movements as $m) {
            $combinedItems->push((object)[
                'kind' => 'movement',
                'date' => Carbon::parse($m->createdAt),
                'data' => $m,
            ]);
        }

        foreach ($auditLogs as $a) {
            $combinedItems->push((object)[
                'kind' => 'audit',
                'date' => Carbon::parse($a->createdAt),
                'data' => $a,
            ]);
        }

        $records = $combinedItems->sortByDesc('date')->values();

        return view('tienda.movimientos', compact('movements', 'auditLogs', 'records'));
    }

    /**
     * Store a new category (restricted to Admins).
     */
    public function storeCategory(Request $request)
    {
        $this->checkAdmin();
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $err = 'Debes seleccionar una sucursal específica para poder crear una categoría.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $err], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $err]);
        }

        $category = ProductCategory::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        AdminAuditLog::record('INSERT', 'product_categories', $category->id, null, $category->toArray(), $gymId);

        $message = 'Categoría de producto creada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function storeProduct(Request $request)
    {
        $this->checkAdmin();
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $err = 'Debes seleccionar una sucursal específica para poder registrar un producto.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $err], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $err]);
        }

        try {
            DB::beginTransaction();

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products'), $filename);
                $imageUrl = 'uploads/products/' . $filename;
            }

            // Note: We create the product with 0 stock_quantity first, because
            // the initial stock movement insert will automatically increment it to
            // the correct value via the DB trigger `trg_update_stock_after_movement`.
            $product = InventoryProduct::create([
                'gym_id' => $gymId,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'cost_price' => $request->cost_price,
                'stock_quantity' => 0,
                'min_stock' => $request->min_stock,
                'image_url' => $imageUrl,
                'is_available' => 1,
            ]);

            // Register initial stock movement (This triggers automatic stock increment in DB)
            if ($request->stock_quantity > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in',
                    'quantity' => $request->stock_quantity,
                    'reason' => 'Carga de stock inicial',
                    'performed_by' => auth()->user()->id,
                ]);
            }

            DB::commit();

            $product->load('category');
            // Re-fetch stock_quantity in case DB trigger updated it
            $product->refresh();

            AdminAuditLog::record('INSERT', 'inventory_products', $product->id, null, $product->toArray(), $gymId);

            $message = 'Producto creado y agregado al inventario exitosamente.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'product' => $product,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al crear producto: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al crear producto: ' . $e->getMessage()]);
        }
    }

    /**
     * Update existing product.
     */
    public function updateProduct(Request $request, $id)
    {
        $this->checkAdmin();
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gymId = $this->getActiveGymId();
        $product = InventoryProduct::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $product->toArray();

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'min_stock' => $request->min_stock,
        ];

        if ($request->hasFile('image')) {
            // Delete old logo file if it exists and is local
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                @unlink(public_path($product->image_url));
            }

            $file = $request->file('image');
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $data['image_url'] = 'uploads/products/' . $filename;
        } elseif ($request->remove_image == '1') {
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                @unlink(public_path($product->image_url));
            }
            $data['image_url'] = null;
        }

        $product->update($data);
        $product->load('category');

        AdminAuditLog::record('UPDATE', 'inventory_products', $product->id, $oldData, $product->fresh()->toArray(), $gymId);

        $message = 'Producto actualizado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable/Enable product availability instead of deleting data physically.
     */
    public function deleteProduct(Request $request, $id)
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();
        $product = InventoryProduct::where('gym_id', $gymId)->findOrFail($id);
        $oldData = $product->toArray();

        $newStatus = $product->is_available ? 0 : 1;
        $product->update([
            'is_available' => $newStatus
        ]);

        AdminAuditLog::record('UPDATE', 'inventory_products', $product->id, $oldData, $product->fresh()->toArray(), $gymId);

        $message = $newStatus 
            ? 'Producto habilitado exitosamente.' 
            : 'Producto inhabilitado exitosamente. Toda la información de inventario y ventas históricas se conserva intacta.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_available' => $newStatus,
                'product_id' => $product->id,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Add stock manually (restristed to Admins).
     */
    public function addStock(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:200',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para poder reabastecer stock.']);
        }

        $product = InventoryProduct::where('gym_id', $gymId)->findOrFail($id);

        // Note: We DO NOT manually increment stock_quantity here in PHP.
        // Creating the InventoryMovement of type 'in' will automatically increment the stock
        // of the product in the database via the trigger `trg_update_stock_after_movement`.
        $movement = InventoryMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'in',
            'quantity' => $request->quantity,
            'reason' => $request->reason ?? 'Reabastecimiento manual',
            'performed_by' => auth()->user()->id,
        ]);

        AdminAuditLog::record('INSERT', 'inventory_movements', $movement->id, null, $movement->toArray(), $gymId);

        $product->refresh();
        $message = 'Stock reabastecido exitosamente (+' . $request->quantity . ').';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product,
                'product_id' => $product->id,
                'new_stock' => $product->stock_quantity,
                'min_stock' => $product->min_stock,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * View sales history (restristed to Admins).
     */
    public function salesHistory()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $salesQuery = ProductSale::with(['client.profile', 'seller.profile', 'items.product', 'promoCode']);
        if ($gymId !== 'all') {
            $salesQuery->where('gym_id', $gymId);
        }
        $sales = $salesQuery->orderBy('id', 'desc')->get();

        return view('tienda.ventas', compact('sales'));
    }

    /**
     * Helper block for role protection.
     */
    private function checkAdmin()
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar almacén y reportes.');
        }
    }
}
