<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\CompanyAccount;
use App\Models\Contract;
use App\Models\Infrastructure;
use App\Models\License;
use App\Models\Resource;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $backend    = User::where('role', 'backend_developer')->first();
        $frontend   = User::where('role', 'frontend_developer')->first();
        $iot        = User::where('role', 'iot_engineer')->first();
        $designer   = User::where('role', 'designer')->first();

        // ═══════════════════════════════════════
        // ASSETS — Physical Hardware
        // ═══════════════════════════════════════
        $assetDefs = [
            ['LAPTOP-001', 'Apple', 'MacBook Pro 14" M3', 'C02XJ2ABHT', '16GB RAM, 512GB SSD, M3 Pro', 'excellent', 32_000_000, $backend, '2024-03-15', 'Apple', '2027-03-15'],
            ['LAPTOP-002', 'Apple', 'MacBook Air 15" M2', 'C02XK3AGHT', '16GB RAM, 256GB SSD, M2', 'good',      28_000_000, $frontend, '2024-06-20', 'Apple', '2026-10-31'],
            ['LAPTOP-003', 'Lenovo', 'ThinkPad X1 Carbon', 'LEN2024X1C', '32GB RAM, 1TB SSD, Core i7', 'good', 22_000_000, $designer, '2023-09-01', 'Lenovo', '2025-12-31'],
            ['LAPTOP-004', 'Asus', 'ROG Zephyrus G16', 'ASUS2024ROG', '32GB RAM, 1TB SSD, RTX 4070', 'excellent', 26_000_000, $iot, '2024-01-10', 'Asus', '2027-01-10'],
            ['MONITOR-001', 'LG', 'UltraWide 34"', 'LG34WN80C', '3440x1440, IPS, 75Hz', 'excellent', 6_500_000, $designer, '2023-05-01', 'LG', '2025-05-01'],
            ['IPHONE-001', 'Apple', 'iPhone 15 Pro', 'SN15PRO001', '256GB, iOS 17, A17 Pro', 'excellent', 18_000_000, $superAdmin, '2024-09-20', 'Apple', '2025-10-15'],
            ['ESP32-KIT-001', 'Espressif', 'ESP32 Dev Kit (x10 units)', 'ESP-DEVKIT-SET1', 'ESP32-WROOM-32D, 38-pin, WiFi+BT', 'good', 1_200_000, $iot, '2026-06-01', null, null],
            ['CAMERA-001', 'Sony', 'ZV-E10 Mirrorless', 'SONYZVE10001', '24.2MP APS-C, 4K Video, Kit Lens', 'good', 8_500_000, null, '2023-11-15', 'Sony', '2025-11-15'],
        ];

        foreach ($assetDefs as [$tag, $brand, $model, $serial, $specs, $cond, $cost, $holder, $purchaseDate, $warrantyProvider, $warrantyExpiry]) {
            $resource = Resource::create([
                'resource_code' => 'RES-' . $tag,
                'name'          => "{$brand} {$model}",
                'category'      => 'asset',
                'owner_id'      => $superAdmin->id,
                'responsible_user_id' => $holder?->id,
                'cost'          => $cost,
                'purchase_date' => $purchaseDate,
                'status'        => $holder ? 'in_use' : 'available',
            ]);

            $asset = Asset::create([
                'resource_id'      => $resource->id,
                'asset_tag'        => $tag,
                'brand'            => $brand,
                'model'            => $model,
                'serial_number'    => $serial,
                'specs'            => $specs,
                'condition'        => $cond,
                'lifecycle_status' => $holder ? 'in_use' : 'available',
                'purchase_cost'    => $cost,
                'accumulated_cost' => $cost,
                'current_holder_id' => $holder?->id,
            ]);

            if ($holder) {
                AssetAssignment::create([
                    'asset_id'               => $asset->id,
                    'user_id'                => $holder->id,
                    'assigned_date'          => $purchaseDate,
                    'condition_on_assignment' => $cond,
                    'notes'                  => 'Initial assignment on purchase',
                ]);
            }

            if ($warrantyProvider && $warrantyExpiry) {
                Warranty::create([
                    'asset_id'    => $asset->id,
                    'provider'    => $warrantyProvider,
                    'start_date'  => $purchaseDate,
                    'expiry_date' => $warrantyExpiry,
                    'terms'       => 'Standard manufacturer warranty — hardware defects covered.',
                ]);
            }
        }

        // Asset with scheduled maintenance
        $laptop3 = Asset::where('asset_tag', 'LAPTOP-003')->first();
        if ($laptop3) {
            AssetMaintenance::create([
                'asset_id'        => $laptop3->id,
                'maintenance_type' => 'inspection',
                'scheduled_date'  => Carbon::now()->addDays(7)->toDateString(),
                'technician_vendor' => 'Lenovo Authorized Service',
                'cost'            => 350_000,
                'notes'           => 'Annual hardware inspection. Battery health check and thermal cleaning.',
                'status'          => 'scheduled',
            ]);
        }

        // ═══════════════════════════════════════
        // INFRASTRUCTURE
        // ═══════════════════════════════════════
        $infraDefs = [
            ['vps', 'DigitalOcean', 'prod-api-01', 'api.solvia.id', '188.166.xxx.xxx', 'Ubuntu 22.04', '4 vCPU, 8GB RAM, 160GB SSD', 'Singapore', 'production', 'Main production API server for all client projects', 350_000, Carbon::now()->addDays(18)->toDateString(), Carbon::now()->addDays(18)->toDateString(), true],
            ['vps', 'DigitalOcean', 'staging-01', 'staging.solvia.id', '165.22.xxx.xxx', 'Ubuntu 22.04', '2 vCPU, 4GB RAM, 80GB SSD', 'Singapore', 'staging', 'Staging environment for client demos and QA', 175_000, Carbon::now()->addDays(18)->toDateString(), Carbon::now()->addDays(18)->toDateString(), true],
            ['domain', 'Namecheap', 'solvia.id', 'solvia.id', null, null, null, null, 'production', 'Primary company domain', 150_000, null, Carbon::now()->addDays(245)->toDateString(), true],
            ['domain', 'Namecheap', 'agritechnusantara.id', 'agritechnusantara.id', null, null, null, null, 'production', 'Client domain — CLT-002 Smart Farm project', 150_000, null, Carbon::now()->addDays(22)->toDateString(), false],
            ['ssl', "Let's Encrypt", 'SSL: api.solvia.id', 'api.solvia.id', null, null, null, null, 'production', 'Wildcard SSL for production API', 0, null, Carbon::now()->addDays(45)->toDateString(), true],
            ['ssl', "Let's Encrypt", 'SSL: staging.solvia.id', 'staging.solvia.id', null, null, null, null, 'staging', 'SSL for staging environment', 0, null, Carbon::now()->addDays(12)->toDateString(), true],
            ['hosting', 'Cloudflare', 'CDN & DNS — solvia.id', 'solvia.id', null, null, null, null, 'production', 'CDN, DDoS protection, and DNS management', 180_000, Carbon::now()->addDays(55)->toDateString(), null, true],
            ['cloud', 'Google Cloud', 'GCS Media Storage', null, null, null, null, 'Singapore', 'production', 'Cloud storage for project media assets and client files', 250_000, Carbon::now()->addDays(18)->toDateString(), null, true],
        ];

        foreach ($infraDefs as [$type, $provider, $name, $hostname, $ip, $os, $specs, $location, $env, $purpose, $monthlyCost, $nextBilling, $expiry, $autoRenew]) {
            $resource = Resource::create([
                'resource_code' => 'RES-INF-' . strtoupper(uniqid()),
                'name'          => $name,
                'category'      => 'infrastructure',
                'owner_id'      => $superAdmin->id,
                'responsible_user_id' => $backend->id,
                'cost'          => $monthlyCost,
                'status'        => 'active',
                'expiry_date'   => $expiry,
                'relevant_date' => $nextBilling,
            ]);

            Infrastructure::create([
                'resource_id'      => $resource->id,
                'type'             => $type,
                'provider'         => $provider,
                'name'             => $name,
                'hostname'         => $hostname,
                'ip_address'       => $ip,
                'os'               => $os,
                'specs'            => $specs,
                'location'         => $location,
                'environment'      => $env,
                'purpose'          => $purpose,
                'encrypted_credentials' => 'SSH: solvia-admin / [encrypted]',
                'monthly_cost'     => $monthlyCost,
                'yearly_cost'      => $monthlyCost * 12,
                'start_date'       => '2024-01-01',
                'next_billing_date' => $nextBilling,
                'expiry_date'      => $expiry,
                'auto_renewal'     => $autoRenew,
                'status'           => 'active',
            ]);
        }

        // ═══════════════════════════════════════
        // SUBSCRIPTIONS
        // ═══════════════════════════════════════
        $subDefs = [
            ['Figma', 'Professional (5 seats)', 225_000, 'monthly', Carbon::now()->addDays(8)->toDateString(), 5, true],
            ['GitHub', 'Team Plan', 155_000, 'monthly', Carbon::now()->addDays(22)->toDateString(), 10, true],
            ['Canva', 'Pro Team', 180_000, 'monthly', Carbon::now()->addDays(15)->toDateString(), 5, true],
            ['Google Workspace', 'Business Starter', 120_000, 'monthly', Carbon::now()->addDays(18)->toDateString(), 7, true],
            ['Adobe Creative Cloud', 'All Apps', 850_000, 'monthly', Carbon::now()->addDays(5)->toDateString(), 1, true],
            ['DigitalOcean', 'Pay-as-you-go', 525_000, 'monthly', Carbon::now()->addDays(18)->toDateString(), 1, true],
            ['Cloudflare', 'Pro Plan', 180_000, 'monthly', Carbon::now()->addDays(55)->toDateString(), 1, true],
            ['Notion', 'Team Plan', 140_000, 'monthly', Carbon::now()->addDays(30)->toDateString(), 10, true],
        ];

        foreach ($subDefs as [$provider, $plan, $cost, $cycle, $nextBilling, $maxUsers, $autoRenew]) {
            $resource = Resource::create([
                'resource_code' => 'RES-SUB-' . strtoupper(substr(md5($provider), 0, 8)),
                'name'          => "{$provider} ({$plan})",
                'category'      => 'subscription',
                'owner_id'      => $superAdmin->id,
                'cost'          => $cost,
                'status'        => 'active',
                'relevant_date' => $nextBilling,
            ]);

            Subscription::create([
                'resource_id'     => $resource->id,
                'provider'        => $provider,
                'plan_name'       => $plan,
                'cost'            => $cost,
                'billing_cycle'   => $cycle,
                'next_billing_date' => $nextBilling,
                'auto_renewal'    => $autoRenew,
                'max_users'       => $maxUsers,
                'status'          => 'active',
            ]);
        }

        // ═══════════════════════════════════════
        // COMPANY ACCOUNTS
        // ═══════════════════════════════════════
        $accountDefs = [
            ['GitHub', 'solvia-nova', true, 'Backup email: devops@solvia.id'],
            ['Figma', 'design@solvia.id', true, '2FA app: Google Authenticator'],
            ['Google Workspace Admin', 'admin@solvia.id', true, 'Recovery email: rian@gmail.com'],
            ['DigitalOcean', 'devops@solvia.id', true, '2FA: Authy app'],
            ['Canva', 'creative@solvia.id', false, null],  // intentionally no 2FA for alert demo
            ['Namecheap', 'domains@solvia.id', false, null], // intentionally no 2FA
            ['Adobe', 'creative@solvia.id', true, 'Backup email linked'],
            ['Cloudflare', 'devops@solvia.id', true, '2FA: TOTP'],
        ];

        foreach ($accountDefs as [$platform, $identifier, $has2fa, $recovery]) {
            $resource = Resource::create([
                'resource_code' => 'RES-ACC-' . strtoupper(substr(md5($platform . $identifier), 0, 8)),
                'name'          => "{$platform} — {$identifier}",
                'category'      => 'account',
                'owner_id'      => $superAdmin->id,
                'status'        => 'active',
            ]);

            CompanyAccount::create([
                'resource_id'           => $resource->id,
                'platform'              => $platform,
                'account_identifier'    => $identifier,
                'encrypted_credentials' => "[ENCRYPTED] Password stored securely",
                'two_factor_status'     => $has2fa,
                'recovery_method'       => $recovery,
                'status'                => 'active',
            ]);
        }

        // ═══════════════════════════════════════
        // LICENSES
        // ═══════════════════════════════════════
        $licenseDefs = [
            ['Windows 11 Pro', 'WIN11-XXXX-XXXX-XXXX-0001', $backend->id, 'LAPTOP-001', '2024-03-15', null, 2_800_000],
            ['Windows 11 Pro', 'WIN11-XXXX-XXXX-XXXX-0002', $frontend->id, 'LAPTOP-002', '2024-06-20', null, 2_800_000],
            ['JetBrains All Products', 'JB-2026-SOLVIA-001', $backend->id, 'MacBook Pro 14" M3', '2026-01-01', Carbon::now()->addDays(110)->toDateString(), 4_200_000],
            ['JetBrains All Products', 'JB-2026-SOLVIA-002', $iot->id, 'ROG Zephyrus G16', '2026-01-01', Carbon::now()->addDays(110)->toDateString(), 4_200_000],
            ['Adobe Premiere Pro', 'ADPR-2026-SOLVIA-001', $designer->id, 'MacBook Air 15"', '2026-09-01', Carbon::now()->addDays(353)->toDateString(), 850_000],
        ];

        foreach ($licenseDefs as [$software, $key, $userId, $device, $purchase, $expiry, $cost]) {
            $resource = Resource::create([
                'resource_code' => 'RES-LIC-' . strtoupper(substr(md5($software . $key), 0, 8)),
                'name'          => $software,
                'category'      => 'license',
                'owner_id'      => $superAdmin->id,
                'cost'          => $cost,
                'status'        => 'active',
                'expiry_date'   => $expiry,
            ]);

            License::create([
                'resource_id'          => $resource->id,
                'software_name'        => $software,
                'license_key_encrypted' => "[ENCRYPTED] {$key}",
                'user_id'              => $userId,
                'device_name'          => $device,
                'purchase_date'        => $purchase,
                'expiry_date'          => $expiry,
                'cost'                 => $cost,
                'status'               => 'active',
            ]);
        }

        // ═══════════════════════════════════════
        // CONTRACTS
        // ═══════════════════════════════════════
        $contracts = [
            ['CTR-2026-001', 'PT Maju Bersama Digital', 'client', '2026-06-01', Carbon::now()->addDays(120)->toDateString(), 275_000_000],
            ['CTR-2026-002', 'CV Agritech Nusantara', 'client', '2026-07-01', '2027-01-31', 185_000_000],
            ['CTR-2026-003', 'PT Logistik Cepat Indonesia', 'client', '2026-08-01', '2027-02-28', 220_000_000],
            ['CTR-2026-004', 'DigitalOcean SLA Agreement', 'vendor', '2024-01-01', Carbon::now()->addDays(50)->toDateString(), 6_300_000],
            ['CTR-2025-005', 'Yayasan Pendidikan Kreatif', 'client', '2025-09-01', Carbon::now()->subDays(10)->toDateString(), 150_000_000],
        ];

        foreach ($contracts as $i => [$num, $party, $type, $start, $expiry, $value]) {
            $resource = Resource::create([
                'resource_code' => 'RES-CTR-' . substr(md5($num), 0, 8),
                'name'          => "{$num} — {$party}",
                'category'      => 'contract',
                'owner_id'      => $superAdmin->id,
                'cost'          => $value,
                'status'        => 'active',
                'expiry_date'   => $expiry,
            ]);

            Contract::create([
                'resource_id'    => $resource->id,
                'contract_number' => $num,
                'party_name'     => $party,
                'party_type'     => $type,
                'start_date'     => $start,
                'expiry_date'    => $expiry,
                'contract_value' => $value,
                'status'         => 'active',
            ]);
        }
    }
}
