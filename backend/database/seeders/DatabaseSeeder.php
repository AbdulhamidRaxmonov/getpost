<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\Terminal;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::firstOrCreate(
            ['phone' => '+998901234567'],
            [
                'name'       => 'Super Admin',
                'email'      => 'admin@yespos.uz',
                'password'   => Hash::make('admin123'),
                'role'       => 'super_admin',
                'is_active'  => true,
            ]
        );

        // Create Demo Organization
        $org = Organization::firstOrCreate(
            ['phone' => '+998990001122'],
            [
                'name'                    => 'YesPOS Demo',
                'legal_name'              => 'YesPOS Demo MCHJ',
                'tin'                     => '123456789',
                'email'                   => 'demo@yespos.uz',
                'address'                 => 'Toshkent sh., Yunusobod tumani',
                'subscription_plan'       => 'pro',
                'subscription_expires_at' => now()->addYear(),
                'is_active'               => true,
            ]
        );

        // Create Main Branch
        $mainBranch = Branch::firstOrCreate(
            ['organization_id' => $org->id, 'is_main' => true],
            [
                'name'      => 'Asosiy filial',
                'address'   => 'Toshkent, Yunusobod',
                'phone'     => '+998990001122',
                'is_active' => true,
            ]
        );

        // Create Terminal
        $terminal = Terminal::firstOrCreate(
            ['organization_id' => $org->id, 'branch_id' => $mainBranch->id, 'name' => 'Kassa-1'],
            ['is_active' => true]
        );

        // Create Org Admin
        User::firstOrCreate(
            ['phone' => '+998990001100'],
            [
                'organization_id' => $org->id,
                'branch_id'       => $mainBranch->id,
                'name'            => 'Demo Admin',
                'email'           => 'demo_admin@yespos.uz',
                'password'        => Hash::make('demo123'),
                'pin'             => '1234',
                'role'            => 'org_admin',
                'is_active'       => true,
            ]
        );

        // Create Cashier
        User::firstOrCreate(
            ['phone' => '+998990001101'],
            [
                'organization_id' => $org->id,
                'branch_id'       => $mainBranch->id,
                'name'            => 'Kassir 1',
                'password'        => Hash::make('demo123'),
                'pin'             => '5678',
                'role'            => 'cashier',
                'is_active'       => true,
            ]
        );

        // Create Units
        $dona = Unit::firstOrCreate(
            ['organization_id' => $org->id, 'short_name' => 'don'],
            ['name' => 'Dona', 'is_fractional' => false]
        );
        $kg = Unit::firstOrCreate(
            ['organization_id' => $org->id, 'short_name' => 'kg'],
            ['name' => 'Kilogram', 'is_fractional' => true]
        );
        $litr = Unit::firstOrCreate(
            ['organization_id' => $org->id, 'short_name' => 'l'],
            ['name' => 'Litr', 'is_fractional' => true]
        );

        // Create Categories
        $ichimliklar = Category::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Ichimliklar'],
            ['color' => '#3B82F6', 'sort_order' => 1, 'is_active' => true]
        );

        $oziqovqat = Category::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Oziq-ovqat'],
            ['color' => '#10B981', 'sort_order' => 2, 'is_active' => true]
        );

        $mevaSabzavot = Category::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Meva-sabzavot'],
            ['color' => '#F59E0B', 'sort_order' => 3, 'is_active' => true]
        );

        // Create Demo Products
        $products = [
            ['sku' => '10001', 'barcode' => '4680001111111', 'name' => 'Olma semerinka kg',  'category' => $mevaSabzavot, 'unit' => $kg,   'purchase_price' => 1200, 'selling_price' => 1500],
            ['sku' => '10002', 'barcode' => '4680001111112', 'name' => 'Uzum xuseyna kg',    'category' => $mevaSabzavot, 'unit' => $kg,   'purchase_price' => 1500, 'selling_price' => 2000],
            ['sku' => '10003', 'barcode' => '4340001111113', 'name' => 'Coca cola 1.5 lite', 'category' => $ichimliklar,  'unit' => $dona, 'purchase_price' => 2500, 'selling_price' => 3125],
            ['sku' => '10004', 'barcode' => '5460001111114', 'name' => 'Fanta 3 liter',      'category' => $ichimliklar,  'unit' => $dona, 'purchase_price' => 2800, 'selling_price' => 3500],
            ['sku' => '10005', 'barcode' => '4680001111115', 'name' => 'Coca cola 0.5 liter','category' => $ichimliklar,  'unit' => $dona, 'purchase_price' => 1500, 'selling_price' => 1950],
            ['sku' => '10006', 'barcode' => '4680001111116', 'name' => 'Ramyon',             'category' => $oziqovqat,    'unit' => $dona, 'purchase_price' => 8000, 'selling_price' => 12000],
            ['sku' => '10007', 'barcode' => '4680001111117', 'name' => 'Yogurt 200g',        'category' => $oziqovqat,    'unit' => $dona, 'purchase_price' => 2500, 'selling_price' => 3500],
            ['sku' => '10008', 'barcode' => '4680001111118', 'name' => 'Non (katta)',        'category' => $oziqovqat,    'unit' => $dona, 'purchase_price' => 3000, 'selling_price' => 4000],
            ['sku' => '10009', 'barcode' => '4680001111119', 'name' => 'Sprite 1.5 litr',   'category' => $ichimliklar,  'unit' => $dona, 'purchase_price' => 2500, 'selling_price' => 3000],
            ['sku' => '10010', 'barcode' => '4680001111120', 'name' => 'Pomidor kg',         'category' => $mevaSabzavot, 'unit' => $kg,   'purchase_price' => 3000, 'selling_price' => 4000],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['organization_id' => $org->id, 'sku' => $p['sku']],
                [
                    'category_id'    => $p['category']->id,
                    'unit_id'        => $p['unit']->id,
                    'barcode'        => $p['barcode'],
                    'name'           => $p['name'],
                    'purchase_price' => $p['purchase_price'],
                    'selling_price'  => $p['selling_price'],
                    'min_price'      => $p['selling_price'] * 0.9,
                    'vat_percent'    => 0,
                    'is_active'      => true,
                    'track_stock'    => true,
                ]
            );

            Stock::firstOrCreate(
                ['product_id' => $product->id, 'branch_id' => $mainBranch->id],
                ['quantity' => rand(10, 100), 'min_quantity' => 5]
            );
        }
    }
}
