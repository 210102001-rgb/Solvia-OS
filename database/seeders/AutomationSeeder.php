<?php

namespace Database\Seeders;

use App\Models\AutomationLog;
use App\Models\AutomationRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AutomationSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name'            => 'Domain Expiry Alert (30 days)',
                'trigger_event'   => 'domain_expiring',
                'condition_config' => json_encode(['days_before' => 30]),
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => 'super_admin',
                    'level'   => 'warning',
                    'message' => 'Domain {{name}} expires in {{days}} days. Renew before {{expiry_date}}.',
                ]),
                'is_active'         => true,
                'last_triggered_at' => Carbon::now()->subDays(2),
            ],
            [
                'name'            => 'Task Overdue Notification',
                'trigger_event'   => 'task_overdue',
                'condition_config' => json_encode(['hours_overdue' => 24]),
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => ['assignee', 'super_admin'],
                    'level'   => 'danger',
                    'message' => 'Task "{{task_title}}" in {{project_name}} is overdue by {{days}} day(s).',
                ]),
                'is_active'         => true,
                'last_triggered_at' => Carbon::now()->subHours(6),
            ],
            [
                'name'            => 'Daily Progress Missing Alert',
                'trigger_event'   => 'progress_missing',
                'condition_config' => json_encode(['check_time' => '18:00', 'grace_minutes' => 30]),
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => ['user', 'super_admin'],
                    'level'   => 'warning',
                    'message' => '{{user_name}} has not submitted daily progress for today.',
                ]),
                'is_active'         => true,
                'last_triggered_at' => Carbon::now()->subHours(18),
            ],
            [
                'name'            => 'Low Inventory Stock Alert',
                'trigger_event'   => 'inventory_low',
                'condition_config' => json_encode(['trigger_at' => 'minimum_stock']),
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => 'super_admin',
                    'level'   => 'warning',
                    'message' => 'Inventory item "{{item_name}}" (SKU: {{sku}}) is below minimum stock. Current: {{current_stock}}, Minimum: {{minimum_stock}}.',
                ]),
                'is_active'         => true,
                'last_triggered_at' => Carbon::now()->subDays(1),
            ],
            [
                'name'            => 'Purchase Request Approved Notification',
                'trigger_event'   => 'purchase_approved',
                'condition_config' => null,
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => 'requester',
                    'level'   => 'success',
                    'message' => 'Your purchase request "{{item_name}}" has been approved by Super Admin.',
                ]),
                'is_active'         => true,
                'last_triggered_at' => Carbon::now()->subDays(3),
            ],
            [
                'name'            => 'Subscription Billing Due (7 days)',
                'trigger_event'   => 'domain_expiring', // reuses expiry trigger
                'condition_config' => json_encode(['days_before' => 7, 'resource_type' => 'subscription']),
                'action_config'   => json_encode([
                    'type'    => 'notify',
                    'target'  => 'super_admin',
                    'level'   => 'info',
                    'message' => 'Subscription {{provider}} ({{plan}}) billing due in {{days}} days. Amount: Rp {{cost}}.',
                ]),
                'is_active' => true,
                'last_triggered_at' => null,
            ],
        ];

        foreach ($rules as $rule) {
            $created = AutomationRule::create($rule);

            // Seed some log entries for active rules
            if ($rule['last_triggered_at']) {
                AutomationLog::create([
                    'automation_rule_id' => $created->id,
                    'trigger_event'      => $rule['trigger_event'],
                    'context_data'       => json_encode(['triggered_by' => 'scheduler', 'items_processed' => rand(1, 5)]),
                    'status'             => 'success',
                    'message'            => "Rule '{$rule['name']}' executed successfully. Notifications dispatched.",
                ]);
            }
        }
    }
}
