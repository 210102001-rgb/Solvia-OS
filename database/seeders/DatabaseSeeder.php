<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CompanySeeder::class,
            ClientSeeder::class,
            FinancialAccountSeeder::class,
            ProjectSeeder::class,
            ResourceSeeder::class,
            FinanceSeeder::class,
            InventorySeeder::class,
            PurchaseSeeder::class,
            AutomationSeeder::class,
            AnnouncementSeeder::class,
            KnowledgeBaseSeeder::class,
        ]);
    }
}
