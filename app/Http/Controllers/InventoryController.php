<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryProduct;
use App\Models\ProductCategory;
use App\Models\ProductSale;
use App\Models\SaleItem;
use App\Models\InventoryMovement;
use App\Models\User;
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
        ]);

        $gymId = $this->getActiveGymId();
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

                // Deduct stock and register inventory movement
                $product->decrement('stock_quantity', $item['quantity']);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'out',
                    'quantity' => $item['quantity'],
                    'reason' => 'Venta POS #' . ($request->user_id ? 'Socio ID ' . $request->user_id : 'Cliente General'),
                    'performed_by' => auth()->user()->id,
                ]);
            }

            // Create product sale record
            $sale = ProductSale::create([
                'gym_id' => $gymId,
                'user_id' => $request->user_id,
                'sold_by' => auth()->user()->id,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Save individual items
            foreach ($itemsToCreate as $item) {
                $item['sale_id'] = $sale->id;
                SaleItem::create($item);
            }

            DB::commit();
            return redirect()->route('tienda.pos')->with('success', 'Venta registrada con éxito. Total cobrado: $' . number_format($totalAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * View Inventory and categories (restristed to Admins).
     */
    public function products()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $products = InventoryProduct::where('gym_id', $gymId)->with('category')->get();
        $categories = ProductCategory::where('gym_id', $gymId)->get();

        return view('tienda.productos', compact('products', 'categories'));
    }

    /**
     * Store a new category (restristed to Admins).
     */
    public function storeCategory(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        ProductCategory::create([
            'gym_id' => $this->getActiveGymId(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Categoría de producto creada exitosamente.');
    }

    /**
     * Store new product (restristed to Admins).
     */
    public function storeProduct(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $gymId = $this->getActiveGymId();

        $product = InventoryProduct::create([
            'gym_id' => $gymId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'stock_quantity' => $request->stock_quantity,
            'min_stock' => $request->min_stock,
            'is_available' => 1,
        ]);

        // Register initial stock movement
        if ($request->stock_quantity > 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'in',
                'quantity' => $request->stock_quantity,
                'reason' => 'Carga de stock inicial',
                'performed_by' => auth()->user()->id,
            ]);
        }

        return redirect()->back()->with('success', 'Producto creado y agregado al inventario.');
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

        $product = InventoryProduct::where('gym_id', $this->getActiveGymId())->findOrFail($id);
        $product->increment('stock_quantity', $request->quantity);

        InventoryMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'in',
            'quantity' => $request->quantity,
            'reason' => $request->reason ?? 'Reabastecimiento manual',
            'performed_by' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Stock actualizado exitosamente.');
    }

    /**
     * View sales history (restristed to Admins).
     */
    public function salesHistory()
    {
        $this->checkAdmin();
        $gymId = $this->getActiveGymId();

        $sales = ProductSale::where('gym_id', $gymId)
            ->with(['client.profile', 'seller.profile', 'items.product'])
            ->orderBy('id', 'desc')
            ->get();

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
