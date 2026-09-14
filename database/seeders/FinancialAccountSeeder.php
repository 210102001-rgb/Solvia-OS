<?php

namespace Database\Seeders;

use App\Models\FinancialAccount;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'account_code' => 'ACC-BCA-001',
                'account_name' => 'BCA Operasional',
                'type' => 'bank',
                'account_number' => '1234567890',
                'bank_name' => 'Bank Central Asia',
                'balance' => 125_000_000.00,
                'is_active' => true,
            ],
            [
                'account_code' => 'ACC-BNI-001',
                'account_name' => 'BNI Payroll',
                'type' => 'bank',
                'account_number' => '9876543210',
                'bank_name' => 'Bank Negara Indonesia',
                'balance' => 85_000_000.00,
                'is_active' => true,
            ],
            [
                'account_code' => 'ACC-CASH-001',
                'account_name' => 'Kas Operasional',
                'type' => 'cash',
                'account_number' => null,
                'bank_name' => null,
                'balance' => 5_500_000.00,
                'is_active' => true,
            ],
            [
                'account_code' => 'ACC-OVO-001',
                'account_name' => 'OVO Bisnis',
                'type' => 'e_wallet',
                'account_number' => '08111234567',
                'bank_name' => 'OVO',
                'balance' => 3_200_000.00,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            FinancialAccount::create($account);
        }
    }
}
