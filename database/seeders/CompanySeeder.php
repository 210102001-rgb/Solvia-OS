<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::create([
            'name' => 'Solvia.Nova',
            'legal_name' => 'PT Solvia Nova Teknologi',
            'email' => 'hello@solvia.id',
            'phone' => '(021) 1234-5678',
            'address' => 'Jl. Sudirman No. 12, Kuningan, Jakarta Selatan, DKI Jakarta 12930',
            'website' => 'https://solvia.id',
            'tax_number' => '12.345.678.9-012.000',
            'currency' => 'IDR',
            'settings' => json_encode([
                'project_health_thresholds' => [
                    'at_risk_progress_gap' => 15,
                    'off_track_progress_gap' => 30,
                    'at_risk_overdue_tasks' => 2,
                    'off_track_overdue_tasks' => 5,
                ],
                'workload_thresholds' => [
                    'high_active_tasks' => 5,
                    'overloaded_active_tasks' => 8,
                ],
            ]),
        ]);
    }
}
