<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payroll;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $backend    = User::where('role', 'backend_developer')->first();
        $frontend   = User::where('role', 'frontend_developer')->first();
        $iot        = User::where('role', 'iot_engineer')->first();
        $designer   = User::where('role', 'designer')->first();
        $content    = User::where('role', 'content_creator')->first();

        $bca     = FinancialAccount::where('account_code', 'ACC-BCA-001')->first();
        $bni     = FinancialAccount::where('account_code', 'ACC-BNI-001')->first();
        $cash    = FinancialAccount::where('account_code', 'ACC-CASH-001')->first();

        $p4 = Project::where('project_code', 'PRJ-2025-004')->first();
        $p1 = Project::where('project_code', 'PRJ-2026-001')->first();
        $p2 = Project::where('project_code', 'PRJ-2026-002')->first();
        $p5 = Project::where('project_code', 'PRJ-2026-005')->first();

        $c1 = Client::where('client_code', 'CLT-001')->first();
        $c2 = Client::where('client_code', 'CLT-002')->first();
        $c4 = Client::where('client_code', 'CLT-004')->first();
        $c5 = Client::where('client_code', 'CLT-005')->first();

        // ═══════════════════════════════════════
        // INCOMES
        // ═══════════════════════════════════════
        $incomes = [
            ['INC-2026-001', '2026-04-10', 'Project Milestone Payment — Phase 1', $c5->id, $p4->id, 75_000_000, 'Client Milestone Payment', $bca->id],
            ['INC-2026-002', '2026-06-15', 'Project Milestone Payment — Phase 2', $c5->id, $p4->id, 75_000_000, 'Client Milestone Payment', $bca->id],
            ['INC-2026-003', '2026-07-20', 'Project Down Payment 30%', $c2->id, $p1->id, 55_500_000, 'Project Down Payment', $bca->id],
            ['INC-2026-004', '2026-08-01', 'Project Down Payment 30%', $c1->id, $p5->id, 82_500_000, 'Project Down Payment', $bca->id],
            ['INC-2026-005', '2026-08-15', 'Project Down Payment 30%', $c4->id, $p2->id, 66_000_000, 'Project Down Payment', $bca->id],
            ['INC-2026-006', '2026-09-01', 'Project Milestone Payment — Phase 1 Completion', $c1->id, $p5->id, 55_000_000, 'Client Milestone Payment', $bca->id],
            ['INC-2026-007', '2026-09-05', 'Retainer Fee — September', $c1->id, null, 15_000_000, 'Monthly Retainer', $bca->id],
        ];

        foreach ($incomes as [$number, $date, $source, $clientId, $projectId, $amount, $category, $accountId]) {
            Income::create([
                'income_number' => $number,
                'date'          => $date,
                'source'        => $source,
                'client_id'     => $clientId,
                'project_id'    => $projectId,
                'amount'        => $amount,
                'category'      => $category,
                'account_id'    => $accountId,
            ]);
        }

        // ═══════════════════════════════════════
        // EXPENSES
        // ═══════════════════════════════════════
        $months = ['2026-07', '2026-08', '2026-09'];
        $expNum = 1;

        // Recurring monthly operational costs
        foreach ($months as $month) {
            $date = $month . '-05';

            // DigitalOcean VPS
            Expense::create([
                'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
                'date'           => $date,
                'category'       => 'infrastructure',
                'amount'         => 525_000,
                'account_id'     => $bca->id,
                'vendor'         => 'DigitalOcean',
                'notes'          => 'VPS prod-api-01 + staging-01 monthly billing',
            ]);

            // Software subscriptions
            Expense::create([
                'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
                'date'           => $date,
                'category'       => 'software',
                'amount'         => 1_650_000, // Figma + GitHub + Canva + Workspace + Notion
                'account_id'     => $bca->id,
                'vendor'         => 'Various SaaS',
                'notes'          => 'Monthly SaaS subscriptions: Figma, GitHub, Canva, Google Workspace, Notion',
            ]);
        }

        // Salary expenses July–August (already paid)
        $salaryMonth = ['2026-07', '2026-08'];
        $salaries = [
            [$backend,  12_000_000],
            [$frontend, 10_500_000],
            [$iot,       9_500_000],
            [$designer,  9_000_000],
            [$content,   7_500_000],
        ];

        foreach ($salaryMonth as $month) {
            foreach ($salaries as [$user, $amount]) {
                Expense::create([
                    'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
                    'date'           => $month . '-28',
                    'category'       => 'salary',
                    'amount'         => $amount,
                    'account_id'     => $bni->id,
                    'vendor'         => $user->name,
                    'notes'          => "Salary payment — {$user->name} — Period {$month}",
                ]);
            }
        }

        // Project-specific costs
        Expense::create([
            'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
            'date'           => '2026-07-10',
            'category'       => 'equipment',
            'project_id'     => $p1->id,
            'amount'         => 5_200_000,
            'account_id'     => $bca->id,
            'vendor'         => 'Tokopedia — IoT Supply',
            'notes'          => 'ESP32 kits, sensors (DHT22, soil moisture, BME280), cables for Smart Farm project',
        ]);

        Expense::create([
            'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
            'date'           => '2026-08-05',
            'category'       => 'infrastructure',
            'project_id'     => $p1->id,
            'amount'         => 2_100_000,
            'account_id'     => $bca->id,
            'vendor'         => 'DigitalOcean',
            'notes'          => 'Dedicated VPS for MQTT broker — Smart Farm project',
        ]);

        Expense::create([
            'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
            'date'           => '2026-06-05',
            'category'       => 'domain',
            'amount'         => 350_000,
            'account_id'     => $bca->id,
            'vendor'         => 'Namecheap',
            'notes'          => 'Domain renewal: solvia.id (1 year)',
        ]);

        Expense::create([
            'expense_number' => sprintf('EXP-2026-%04d', $expNum++),
            'date'           => '2026-08-20',
            'category'       => 'transportation',
            'project_id'     => $p1->id,
            'amount'         => 850_000,
            'account_id'     => $cash->id,
            'vendor'         => 'Pertamina / Transportation',
            'notes'          => 'Site visit to Agritech farm for sensor deployment — fuel and toll',
        ]);

        // ═══════════════════════════════════════
        // INVOICES
        // ═══════════════════════════════════════

        // Invoice 1 — Paid (Yayasan PK final)
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-001',
            'client_id'      => $c5->id,
            'project_id'     => $p4->id,
            'issue_date'     => '2026-04-05',
            'due_date'       => '2026-04-20',
            'subtotal'       => 75_000_000,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 75_000_000,
            'payment_status' => 'paid',
            'paid_at'        => '2026-04-10',
            'notes'          => 'Milestone 1 payment — E-Learning Platform',
        ]);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'E-Learning Platform — Phase 1 Delivery', 'quantity' => 1, 'unit_price' => 75_000_000, 'total_price' => 75_000_000]);

        // Invoice 2 — Paid
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-2026-002',
            'client_id'      => $c5->id,
            'project_id'     => $p4->id,
            'issue_date'     => '2026-06-10',
            'due_date'       => '2026-06-25',
            'subtotal'       => 75_000_000,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 75_000_000,
            'payment_status' => 'paid',
            'paid_at'        => '2026-06-15',
            'notes'          => 'Milestone 2 payment — E-Learning Platform',
        ]);
        InvoiceItem::create(['invoice_id' => $inv2->id, 'description' => 'E-Learning Platform — Phase 2 Delivery', 'quantity' => 1, 'unit_price' => 75_000_000, 'total_price' => 75_000_000]);

        // Invoice 3 — Sent (Smart Farm DP)
        $inv3 = Invoice::create([
            'invoice_number' => 'INV-2026-003',
            'client_id'      => $c2->id,
            'project_id'     => $p1->id,
            'issue_date'     => '2026-07-15',
            'due_date'       => '2026-07-30',
            'subtotal'       => 55_500_000,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 55_500_000,
            'payment_status' => 'paid',
            'paid_at'        => '2026-07-20',
            'notes'          => 'Down payment 30% — Smart Farm Monitoring System',
        ]);
        InvoiceItem::create(['invoice_id' => $inv3->id, 'description' => 'Smart Farm System — Down Payment 30%', 'quantity' => 1, 'unit_price' => 55_500_000, 'total_price' => 55_500_000]);

        // Invoice 4 — Overdue (Enterprise Dashboard)
        $inv4 = Invoice::create([
            'invoice_number' => 'INV-2026-004',
            'client_id'      => $c1->id,
            'project_id'     => $p5->id,
            'issue_date'     => '2026-08-01',
            'due_date'       => Carbon::now()->subDays(8)->toDateString(), // overdue
            'subtotal'       => 82_500_000,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 82_500_000,
            'payment_status' => 'overdue',
            'notes'          => 'Milestone 1 — Enterprise Dashboard. Payment overdue.',
        ]);
        InvoiceItem::create(['invoice_id' => $inv4->id, 'description' => 'Enterprise Dashboard — Milestone 1: Data Integration Layer', 'quantity' => 1, 'unit_price' => 82_500_000, 'total_price' => 82_500_000]);

        // Invoice 5 — Sent (Logistik Mobile DP)
        $inv5 = Invoice::create([
            'invoice_number' => 'INV-2026-005',
            'client_id'      => $c4->id,
            'project_id'     => $p2->id,
            'issue_date'     => '2026-08-10',
            'due_date'       => Carbon::now()->addDays(3)->toDateString(),
            'subtotal'       => 66_000_000,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 66_000_000,
            'payment_status' => 'sent',
            'notes'          => 'Down payment 30% — Logistik Cepat Mobile App',
        ]);
        InvoiceItem::create(['invoice_id' => $inv5->id, 'description' => 'Mobile App Development — Down Payment 30%', 'quantity' => 1, 'unit_price' => 66_000_000, 'total_price' => 66_000_000]);

        // Invoice 6 — Draft (Yayasan PK final balance)
        $inv6 = Invoice::create([
            'invoice_number' => 'INV-2026-006',
            'client_id'      => $c5->id,
            'project_id'     => $p4->id,
            'issue_date'     => Carbon::now()->toDateString(),
            'due_date'       => Carbon::now()->addDays(14)->toDateString(),
            'subtotal'       => 0, // items determine this
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 0,
            'payment_status' => 'draft',
            'notes'          => 'Final balance payment — E-Learning Platform. Awaiting client sign-off.',
        ]);

        // ═══════════════════════════════════════
        // PAYROLL — July & August paid
        // ═══════════════════════════════════════
        $payrollUsers = [
            [$backend,  12_000_000, 1_500_000],
            [$frontend, 10_500_000, 1_200_000],
            [$iot,       9_500_000, 1_200_000],
            [$designer,  9_000_000, 1_000_000],
            [$content,   7_500_000,   800_000],
        ];

        foreach (['2026-07', '2026-08'] as $period) {
            foreach ($payrollUsers as [$user, $base, $allowance]) {
                $total = $base + $allowance;
                Payroll::create([
                    'user_id'        => $user->id,
                    'period'         => $period,
                    'base_salary'    => $base,
                    'allowance'      => $allowance,
                    'bonus'          => 0,
                    'deduction'      => 0,
                    'reimbursement'  => 0,
                    'total'          => $total,
                    'payment_status' => 'paid',
                    'paid_at'        => $period . '-28',
                    'notes'          => "Regular payroll — {$period}",
                ]);
            }
        }

        // September payroll — approved not yet paid
        foreach ($payrollUsers as [$user, $base, $allowance]) {
            $total = $base + $allowance;
            Payroll::create([
                'user_id'        => $user->id,
                'period'         => '2026-09',
                'base_salary'    => $base,
                'allowance'      => $allowance,
                'bonus'          => 0,
                'deduction'      => 0,
                'reimbursement'  => 0,
                'total'          => $total,
                'payment_status' => 'approved',
                'notes'          => 'Payroll September 2026 — pending disbursement',
            ]);
        }

        // ═══════════════════════════════════════
        // REIMBURSEMENTS
        // ═══════════════════════════════════════
        Reimbursement::create([
            'user_id'    => $iot->id,
            'project_id' => $p1->id,
            'title'      => 'Biaya Perjalanan Site Visit — Farm Agritech',
            'amount'     => 850_000,
            'date'       => '2026-08-20',
            'category'   => 'transportation',
            'status'     => 'paid',
            'approver_id' => $superAdmin->id,
            'notes'      => 'Round trip Jakarta–Bandung for sensor deployment. Toll + BBM + parking.',
        ]);

        Reimbursement::create([
            'user_id'    => $backend->id,
            'project_id' => $p5->id,
            'title'      => 'Pembelian Domain Testing — logistik-staging.id',
            'amount'     => 180_000,
            'date'       => '2026-09-02',
            'category'   => 'software',
            'status'     => 'approved',
            'approver_id' => $superAdmin->id,
            'notes'      => 'Staging domain for Logistik Cepat project testing environment.',
        ]);

        Reimbursement::create([
            'user_id'    => $designer->id,
            'project_id' => null,
            'title'      => 'Pembelian Font License — Inter Variable',
            'amount'     => 350_000,
            'date'       => Carbon::now()->subDays(2)->toDateString(),
            'category'   => 'software',
            'status'     => 'submitted',
            'notes'      => 'Variable font license for use across all client projects and design system.',
        ]);

        Reimbursement::create([
            'user_id'    => $content->id,
            'project_id' => null,
            'title'      => 'Freepik Pro Monthly — Content Assets',
            'amount'     => 120_000,
            'date'       => Carbon::now()->subDays(1)->toDateString(),
            'category'   => 'operational',
            'status'     => 'submitted',
            'notes'      => 'Freepik Pro for stock photos and vectors used in social media content.',
        ]);

        // ═══════════════════════════════════════
        // BUDGETS
        // ═══════════════════════════════════════
        $year = 2026;

        // Company-wide annual
        Budget::create(['scope_type' => 'company', 'year' => $year, 'month' => null, 'category' => null, 'budgeted_amount' => 800_000_000, 'actual_amount' => 345_000_000]);

        // Category-based annual
        Budget::create(['scope_type' => 'category', 'year' => $year, 'month' => null, 'category' => 'salary', 'budgeted_amount' => 600_000_000, 'actual_amount' => 244_000_000]);
        Budget::create(['scope_type' => 'category', 'year' => $year, 'month' => null, 'category' => 'infrastructure', 'budgeted_amount' => 30_000_000, 'actual_amount' => 15_400_000]);
        Budget::create(['scope_type' => 'category', 'year' => $year, 'month' => null, 'category' => 'software', 'budgeted_amount' => 25_000_000, 'actual_amount' => 16_800_000]);
        Budget::create(['scope_type' => 'category', 'year' => $year, 'month' => null, 'category' => 'marketing', 'budgeted_amount' => 20_000_000, 'actual_amount' => 4_500_000]);
        Budget::create(['scope_type' => 'category', 'year' => $year, 'month' => null, 'category' => 'equipment', 'budgeted_amount' => 50_000_000, 'actual_amount' => 36_200_000]);

        // Project-specific budgets
        Budget::create(['scope_type' => 'project', 'year' => $year, 'month' => null, 'category' => null, 'project_id' => $p1->id, 'budgeted_amount' => 110_000_000, 'actual_amount' => 67_000_000]);
        Budget::create(['scope_type' => 'project', 'year' => $year, 'month' => null, 'category' => null, 'project_id' => $p2->id, 'budgeted_amount' => 130_000_000, 'actual_amount' => 42_000_000]);
        Budget::create(['scope_type' => 'project', 'year' => $year, 'month' => null, 'category' => null, 'project_id' => $p5->id, 'budgeted_amount' => 160_000_000, 'actual_amount' => 145_000_000]);
    }
}
