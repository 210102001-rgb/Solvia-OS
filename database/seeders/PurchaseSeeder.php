<?php

namespace Database\Seeders;

use App\Models\PurchaseRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $backend    = User::where('role', 'backend_developer')->first();
        $frontend   = User::where('role', 'frontend_developer')->first();
        $iot        = User::where('role', 'iot_engineer')->first();
        $designer   = User::where('role', 'designer')->first();

        $requests = [
            [
                'request_number'  => 'REQ-2026-001',
                'requester_id'    => $iot->id,
                'item_name'       => 'Arduino Nano x10 pcs',
                'category'        => 'inventory',
                'quantity'        => 10,
                'estimated_cost'  => 650_000,
                'reason'          => 'Arduino Nano stock depleted. Required for ongoing IoT prototyping and Smart Farm fallback nodes.',
                'priority'        => 'high',
                'status'          => 'submitted',
            ],
            [
                'request_number'  => 'REQ-2026-002',
                'requester_id'    => $designer->id,
                'item_name'       => 'Wacom Intuos Pro Medium Drawing Tablet',
                'category'        => 'asset',
                'quantity'        => 1,
                'estimated_cost'  => 3_500_000,
                'reason'          => 'Current trackpad workflow slows down detailed icon and illustration work. A drawing tablet will significantly improve design quality and speed.',
                'priority'        => 'medium',
                'status'          => 'approved',
                'approver_id'     => $superAdmin->id,
                'approved_at'     => Carbon::now()->subDays(3)->toDateString(),
            ],
            [
                'request_number'  => 'REQ-2026-003',
                'requester_id'    => $backend->id,
                'item_name'       => 'USB-C Dock Station — 12-in-1',
                'category'        => 'asset',
                'quantity'        => 1,
                'estimated_cost'  => 650_000,
                'reason'          => 'Need multi-monitor setup for dual-screen development. Current USB-C hub only supports single 4K output.',
                'priority'        => 'low',
                'status'          => 'purchased',
                'approver_id'     => $superAdmin->id,
                'approved_at'     => Carbon::now()->subDays(7)->toDateString(),
                'actual_cost'     => 625_000,
            ],
            [
                'request_number'  => 'REQ-2026-004',
                'requester_id'    => $frontend->id,
                'item_name'       => 'Second Monitor — BenQ 27" IPS 4K',
                'category'        => 'asset',
                'quantity'        => 1,
                'estimated_cost'  => 5_200_000,
                'reason'          => 'Dual monitor setup needed for more efficient frontend development and Figma review workflows.',
                'priority'        => 'medium',
                'status'          => 'received',
                'approver_id'     => $superAdmin->id,
                'approved_at'     => Carbon::now()->subDays(12)->toDateString(),
                'actual_cost'     => 4_950_000,
            ],
            [
                'request_number'  => 'REQ-2026-005',
                'requester_id'    => $iot->id,
                'item_name'       => 'Sensor Kelembaban Tanah x20 pcs + Kabel Jumper Set',
                'category'        => 'inventory',
                'quantity'        => 20,
                'estimated_cost'  => 900_000,
                'reason'          => 'Soil moisture sensor stock running low (current: 6, minimum: 5). Phase 2 of Smart Farm deployment requires 15 additional sensors.',
                'priority'        => 'urgent',
                'status'          => 'submitted',
            ],
        ];

        foreach ($requests as $request) {
            PurchaseRequest::create($request);
        }
    }
}
