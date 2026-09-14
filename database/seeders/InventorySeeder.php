<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $iot        = User::where('role', 'iot_engineer')->first();

        $items = [
            // Network hardware
            ['INV-NET-001', 'Kabel LAN Cat6 (1m)', 'Network',   'pcs',    45,  10, 'Storage Room A', 'Tokopedia',  8_000],
            ['INV-NET-002', 'Kabel LAN Cat6 (2m)', 'Network',   'pcs',    28,  10, 'Storage Room A', 'Tokopedia', 12_000],
            ['INV-NET-003', 'RJ45 Connector',      'Network',   'pcs',   120,  50, 'Storage Room A', 'Tokopedia',    500],
            ['INV-NET-004', 'Switch 8-Port',       'Network',   'unit',    3,   2, 'Storage Room A', 'Tokopedia', 85_000],
            // IoT & Electronics
            ['INV-IOT-001', 'ESP32 Dev Kit',         'Electronics', 'pcs',  8,   5, 'IoT Lab', 'Tokopedia',  75_000],
            ['INV-IOT-002', 'Sensor DHT22',          'Electronics', 'pcs',  3,   5, 'IoT Lab', 'Tokopedia',  45_000], // low stock
            ['INV-IOT-003', 'Soil Moisture Sensor',  'Electronics', 'pcs',  6,   5, 'IoT Lab', 'Shopee',     35_000],
            ['INV-IOT-004', 'Raspberry Pi 4 (4GB)',  'Electronics', 'unit', 2,   1, 'IoT Lab', 'Bhinneka', 750_000],
            ['INV-IOT-005', 'Arduino Nano',          'Electronics', 'pcs',  0,   3, 'IoT Lab', 'Tokopedia', 65_000], // out of stock
            // Office supplies
            ['INV-OFF-001', 'Kertas A4 (500 lembar)', 'Office',  'rim',  12,   5, 'Office Supply Cabinet', 'Alfamart',  45_000],
            ['INV-OFF-002', 'Spidol Whiteboard',       'Office',  'pcs',   4,   6, 'Office Supply Cabinet', 'Gramedia',   8_000], // low stock
            ['INV-OFF-003', 'HDMI Cable 2m',           'Cable',   'pcs',   8,   3, 'Storage Room A', 'Tokopedia',  35_000],
            ['INV-OFF-004', 'USB-C Hub 7-in-1',        'Accessory','pcs',  3,   2, 'Storage Room A', 'Tokopedia', 185_000],
            // Packaging & printing
            ['INV-MKT-001', 'Stiker Logo Solvia',     'Marketing', 'lembar', 200, 50, 'Creative Storage', 'Percetakan Jaya', 500],
        ];

        foreach ($items as [$sku, $name, $cat, $unit, $stock, $minStock, $location, $supplier, $cost]) {
            $status = match(true) {
                $stock === 0 => 'out_of_stock',
                $stock <= $minStock => 'low_stock',
                default => 'in_stock',
            };

            $item = InventoryItem::create([
                'sku'           => $sku,
                'item_name'     => $name,
                'category'      => $cat,
                'unit'          => $unit,
                'current_stock' => $stock,
                'minimum_stock' => $minStock,
                'location'      => $location,
                'supplier'      => $supplier,
                'unit_cost'     => $cost,
                'status'        => $status,
            ]);

            // Opening stock transaction
            if ($stock > 0) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'user_id'           => $superAdmin->id,
                    'transaction_type'  => 'stock_in',
                    'quantity'          => $stock,
                    'balance_after'     => $stock,
                    'notes'             => 'Opening stock — initial registration',
                ]);
            }
        }

        // Simulate stock movements for IoT items
        $dht22 = InventoryItem::where('sku', 'INV-IOT-002')->first();
        $esp32 = InventoryItem::where('sku', 'INV-IOT-001')->first();

        if ($dht22) {
            InventoryTransaction::create([
                'inventory_item_id' => $dht22->id,
                'user_id'           => $iot->id,
                'transaction_type'  => 'usage',
                'quantity'          => 5,
                'balance_after'     => 3,
                'notes'             => 'Used for Smart Farm sensor deployment — PRJ-2026-001 Phase 1',
            ]);
        }

        if ($esp32) {
            InventoryTransaction::create([
                'inventory_item_id' => $esp32->id,
                'user_id'           => $iot->id,
                'transaction_type'  => 'usage',
                'quantity'          => 2,
                'balance_after'     => 8,
                'notes'             => 'ESP32 used for Smart Farm sensor nodes — PRJ-2026-001',
            ]);
        }
    }
}
