<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProductCategory;
use App\Models\InventoryProduct;
use App\Models\ProductSale;
use App\Models\SaleItem;
use App\Models\User;

class StoreAndInventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Product Categories
        $categoriesData = [
            ['id' => 1, 'name' => 'Proteínas & Suplementos', 'desc' => 'Whey Protein, Creatina, BCAA, Pre-Entrenos.', 'icon' => 'bottle-droplet'],
            ['id' => 2, 'name' => 'Bebidas & Hidratación', 'desc' => 'Agua mineral, Isotónicas, Energizantes.', 'icon' => 'glass-water'],
            ['id' => 3, 'name' => 'Snacks & Barras Proteicas', 'desc' => 'Barras de proteína, frutos secos, galletas fit.', 'icon' => 'cookie'],
            ['id' => 4, 'name' => 'Ropa Deportivo & Merch', 'desc' => 'Camisetas, tops, straps, guantes, toallas.', 'icon' => 'shirt'],
            ['id' => 5, 'name' => 'Accesorios & Equipamiento', 'desc' => 'Shakers, cinturones de fuerza, bandas de resistencia.', 'icon' => 'dumbbell'],
        ];

        foreach ($categoriesData as $cat) {
            ProductCategory::create([
                'id' => $cat['id'],
                'gym_id' => null,
                'name' => $cat['name'],
                'description' => $cat['desc'],
                'icon_url' => $cat['icon'],
            ]);
        }

        // 2. 50 Inventory Products (High initial stock to pass trigger checks on sales)
        $productsCatalog = [
            ['cat' => 1, 'name' => 'Whey Protein Isolate 2lb (Vainilla)', 'price' => 49.99, 'cost' => 30.00, 'food' => 1],
            ['cat' => 1, 'name' => 'Creatina Monohidratada Creapure 300g', 'price' => 29.99, 'cost' => 15.00, 'food' => 1],
            ['cat' => 1, 'name' => 'Pre-Workout C4 Explosive 30 serv.', 'price' => 34.99, 'cost' => 20.00, 'food' => 1],
            ['cat' => 1, 'name' => 'BCAA 2:1:1 Aminoácidos 250g', 'price' => 24.99, 'cost' => 12.00, 'food' => 1],
            ['cat' => 2, 'name' => 'Gatorade / Powerade 500ml', 'price' => 2.50, 'cost' => 1.00, 'food' => 1],
            ['cat' => 2, 'name' => 'Agua Mineral Evian 750ml', 'price' => 1.50, 'cost' => 0.50, 'food' => 1],
            ['cat' => 3, 'name' => 'Barra Proteica Quest Bar 60g', 'price' => 3.50, 'cost' => 1.80, 'food' => 1],
            ['cat' => 3, 'name' => 'Mix de Frutos Secos Fit 100g', 'price' => 2.00, 'cost' => 0.90, 'food' => 1],
            ['cat' => 4, 'name' => 'Camiseta Oficial GymFlow Dry-Fit', 'price' => 19.99, 'cost' => 8.00, 'food' => 0],
            ['cat' => 5, 'name' => 'Shaker Mezclador Pro 700ml', 'price' => 8.99, 'cost' => 3.50, 'food' => 0],
        ];

        $products = [];
        $prodId = 1;

        for ($gymId = 1; $gymId <= 5; $gymId++) {
            foreach ($productsCatalog as $pc) {
                $product = InventoryProduct::create([
                    'id' => $prodId,
                    'gym_id' => $gymId,
                    'category_id' => $pc['cat'],
                    'name' => $pc['name'],
                    'description' => "Producto oficial en tienda G{$gymId}.",
                    'sku' => "PROD-G{$gymId}-" . sprintf('%04d', $prodId),
                    'price' => $pc['price'],
                    'cost_price' => $pc['cost'],
                    'currency' => 'USD',
                    'stock_quantity' => 500, // Alto stock para evitar bloqueo por trigger
                    'min_stock' => 10,
                    'unit' => 'unidad',
                    'image_url' => 'https://images.unsplash.com/photo-1579722821273-0f6c7d44362f?q=80&w=200',
                    'is_available' => 1,
                    'is_food' => $pc['food'],
                    'createdAt' => $now->copy()->subMonths(6),
                    'updatedAt' => $now,
                ]);

                // Initial stock movement IN
                $admin = User::where('gym_id', $gymId)->where('role', 'admin')->first();
                $adminId = $admin ? $admin->id : 1;

                DB::table('inventory_movements')->insert([
                    'product_id' => $product->id,
                    'movement_type' => 'in',
                    'quantity' => 500,
                    'previous_stock' => 0,
                    'new_stock' => 500,
                    'reason' => 'Inventario inicial de apertura de tienda',
                    'reference_id' => null,
                    'performed_by' => $adminId,
                    'createdAt' => $now->copy()->subMonths(6),
                ]);

                $products[$gymId][] = $product;
                $prodId++;
            }
        }

        // 3. Product Sales + Sale Items (300+ sales, 800+ items sold)
        $paymentMethods = ['cash', 'card', 'transfer', 'other'];

        for ($gymId = 1; $gymId <= 5; $gymId++) {
            $gymProducts = $products[$gymId];
            $gymMembers = User::where('gym_id', $gymId)->where('role', 'member')->get();
            $admin = User::where('gym_id', $gymId)->where('role', 'admin')->first();
            $adminId = $admin ? $admin->id : 1;

            // Generate 60 sales per gym = 300 total sales
            for ($saleIndex = 1; $saleIndex <= 60; $saleIndex++) {
                $member = $gymMembers[$saleIndex % count($gymMembers)];
                $saleDate = $now->copy()->subDays(60 - $saleIndex);

                $sale = ProductSale::create([
                    'gym_id' => $gymId,
                    'user_id' => $member->id,
                    'promo_code_id' => null,
                    'sold_by' => $adminId,
                    'total_amount' => 0.00, // Will update after adding items
                    'payment_method' => $paymentMethods[$saleIndex % count($paymentMethods)],
                    'sale_date' => $saleDate->toDateTimeString(),
                    'notes' => 'Venta en mostrador procesada con éxito.',
                    'createdAt' => $saleDate,
                ]);

                $totalAmount = 0.00;
                // Add 2-3 items per sale
                for ($itemIdx = 0; $itemIdx < 2; $itemIdx++) {
                    $prod = $gymProducts[($saleIndex + $itemIdx) % count($gymProducts)];
                    $qty = rand(1, 2);
                    $subtotal = round($prod->price * $qty, 2);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $prod->id,
                        'quantity' => $qty,
                        'unit_price' => $prod->price,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;
                }

                $sale->update(['total_amount' => $totalAmount]);
            }
        }
    }
}
