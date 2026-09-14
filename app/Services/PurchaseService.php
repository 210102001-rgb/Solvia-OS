<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseRequest;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Super Admin approves a purchase request.
     */
    public function approve(PurchaseRequest $request, User $approver): void
    {
        $request->update([
            'status' => 'approved',
            'approver_id' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        AuditLogger::log('approve', 'PurchaseRequest', $request->id, null, ['status' => 'approved'], "Purchase request {$request->request_number} approved by {$approver->name}");
    }

    /**
     * Mark request as purchased with actual final cost.
     */
    public function markPurchased(PurchaseRequest $request, float $actualCost): void
    {
        $request->update([
            'status' => 'purchased',
            'actual_cost' => $actualCost,
        ]);
    }

    /**
     * Super Admin receives goods and registers them directly into Asset or Inventory,
     * and automatically creates the Finance Expense record!
     */
    public function receiveAndRegister(PurchaseRequest $request, array $options = []): void
    {
        DB::transaction(function () use ($request, $options) {
            $cost = $request->actual_cost ?? $request->estimated_cost;
            $defaultAccount = FinancialAccount::firstOrCreate(
                ['account_code' => 'ACC-OPR'],
                ['account_name' => 'Operational Bank Account', 'type' => 'bank', 'balance' => 50000000]
            );

            // 1. Create Finance Expense
            $expense = Expense::create([
                'expense_number' => 'EXP-' . strtoupper(uniqid()),
                'date' => Carbon::now()->toDateString(),
                'category' => $request->category === 'asset' ? 'equipment' : 'operational',
                'amount' => $cost,
                'account_id' => $defaultAccount->id,
                'vendor' => $options['vendor'] ?? 'Authorized Vendor',
                'notes' => "Automatic expense from purchase request: {$request->request_number} ({$request->item_name})",
            ]);

            // Deduct account balance
            $defaultAccount->decrement('balance', $cost);
            $request->expense_id = $expense->id;

            // 2. If Asset -> Register Asset in Resource Registry & Asset catalog
            if ($request->category === 'asset') {
                $tagNumber = str_pad(Asset::count() + 1, 3, '0', STR_PAD_LEFT);
                $resourceCode = 'RES-AST-' . $tagNumber;

                $resource = Resource::create([
                    'resource_code' => $resourceCode,
                    'name' => $request->item_name,
                    'category' => 'asset',
                    'owner_id' => $request->requester_id,
                    'responsible_user_id' => $request->approver_id ?? $request->requester_id,
                    'cost' => $cost,
                    'purchase_date' => Carbon::now()->toDateString(),
                    'status' => 'available',
                    'location' => $options['location'] ?? 'Headquarters',
                    'notes' => "Purchased via request {$request->request_number}",
                ]);

                $asset = Asset::create([
                    'resource_id' => $resource->id,
                    'asset_tag' => 'AST-' . $tagNumber,
                    'brand' => $options['brand'] ?? 'Solvia Equipment',
                    'model' => $options['model'] ?? $request->item_name,
                    'serial_number' => $options['serial_number'] ?? 'SN-' . strtoupper(uniqid()),
                    'specs' => $options['specs'] ?? $request->reason,
                    'condition' => 'excellent',
                    'lifecycle_status' => 'available',
                    'purchase_cost' => $cost,
                    'accumulated_cost' => $cost,
                ]);

                $request->created_asset_id = $asset->id;
            }

            // 3. If Inventory -> Create or increment inventory item & record stock transaction
            if ($request->category === 'inventory') {
                $sku = $options['sku'] ?? 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->item_name), 0, 6)) . '-' . rand(100, 999);
                
                $inventory = InventoryItem::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'item_name' => $request->item_name,
                        'category' => 'Hardware',
                        'unit' => $options['unit'] ?? 'pcs',
                        'current_stock' => 0,
                        'minimum_stock' => 5,
                        'unit_cost' => round($cost / max(1, $request->quantity), 2),
                    ]
                );

                $inventory->increment('current_stock', $request->quantity);
                $inventory->updateStockStatus();

                InventoryTransaction::create([
                    'inventory_item_id' => $inventory->id,
                    'user_id' => $request->requester_id,
                    'transaction_type' => 'stock_in',
                    'quantity' => $request->quantity,
                    'balance_after' => $inventory->current_stock,
                    'notes' => "Stock in from Purchase Request {$request->request_number}",
                ]);

                $request->created_inventory_id = $inventory->id;
            }

            $request->status = 'registered';
            $request->save();

            AuditLogger::log('create', 'PurchaseRequest', $request->id, null, $request->toArray(), "Purchase request {$request->request_number} received, registered, and converted to expense.");
        });
    }
}
