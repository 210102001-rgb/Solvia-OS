<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\CompanyAccount;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Infrastructure;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\License;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Resource;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Warranty;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::with('owner', 'responsibleUser', 'project', 'team')
            ->orderByDesc('created_at')
            ->paginate(20);

        $counts = [
            'total' => Resource::count(),
            'assets' => Asset::count(),
            'infrastructure' => Infrastructure::count(),
            'subscriptions' => Subscription::count(),
            'accounts' => CompanyAccount::count(),
            'inventory' => InventoryItem::count(),
        ];

        return view('resources.index', compact('resources', 'counts'));
    }

    public function assets()
    {
        $user = Auth::user();
        $query = Asset::with('resource', 'currentHolder', 'warranty', 'assignments.user');

        if (!$user->isSuperAdmin()) {
            $query->where('current_holder_id', $user->id);
        }

        $assets = $query->paginate(20);
        $users = User::where('status', 'active')->get();
        $totalAssetValue = $user->isSuperAdmin() ? Asset::sum('accumulated_cost') : 0;

        return view('resources.assets', compact('assets', 'users', 'totalAssetValue'));
    }

    public function storeAsset(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        foreach (['serial_number', 'specs', 'warranty_provider', 'warranty_expiry'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        if (!$isSuperAdmin) {
            $request->merge([
                'current_holder_id' => $user->id,
                'purchase_cost' => 0,
            ]);
        } elseif (!$request->filled('current_holder_id')) {
            $request->merge(['current_holder_id' => null]);
        }

        $data = $request->validate([
            'asset_tag' => 'required|string|unique:assets,asset_tag',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'specs' => 'nullable|string',
            'condition' => 'required|in:excellent,good,fair,damaged',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_holder_id' => 'nullable|exists:users,id',
            'warranty_provider' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
        ]);

        $data['purchase_cost'] = (float) ($data['purchase_cost'] ?? 0);

        DB::transaction(function () use ($data) {
            $resource = Resource::create([
                'resource_code' => 'RES-' . $data['asset_tag'],
                'name' => "{$data['brand']} {$data['model']}",
                'category' => 'asset',
                'cost' => $data['purchase_cost'],
                'purchase_date' => Carbon::now()->toDateString(),
                'status' => $data['current_holder_id'] ? 'in_use' : 'available',
            ]);

            $asset = Asset::create([
                'resource_id' => $resource->id,
                'asset_tag' => $data['asset_tag'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'serial_number' => $data['serial_number'] ?? null,
                'specs' => $data['specs'] ?? null,
                'condition' => $data['condition'],
                'lifecycle_status' => $data['current_holder_id'] ? 'in_use' : 'available',
                'purchase_cost' => $data['purchase_cost'],
                'accumulated_cost' => $data['purchase_cost'],
                'current_holder_id' => $data['current_holder_id'] ?? null,
            ]);

            if ($data['current_holder_id']) {
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'user_id' => $data['current_holder_id'],
                    'assigned_date' => Carbon::now()->toDateString(),
                    'condition_on_assignment' => $data['condition'],
                    'notes' => 'Initial assignment on registration',
                ]);

                Notification::create([
                    'user_id' => $data['current_holder_id'],
                    'type' => 'asset',
                    'title' => 'Penugasan Perangkat: ' . $asset->asset_tag,
                    'message' => Auth::user()->name . " menugaskan perangkat {$asset->brand} {$asset->model} ({$asset->asset_tag}) kepada Anda.",
                    'action_url' => route('resources.assets'),
                    'level' => 'info',
                ]);
            }

            if (!empty($data['warranty_provider']) && !empty($data['warranty_expiry'])) {
                Warranty::create([
                    'asset_id' => $asset->id,
                    'provider' => $data['warranty_provider'],
                    'start_date' => Carbon::now()->toDateString(),
                    'expiry_date' => $data['warranty_expiry'],
                ]);
            }

            AuditLogger::log('create', 'Asset', $asset->id, null, $asset->toArray(), "Registered asset {$asset->asset_tag} ({$asset->brand} {$asset->model})");
        });

        return back()->with('success', 'Asset created and registered in Resource Registry.');
    }

    public function assignAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'condition' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $asset->update([
            'current_holder_id' => $data['user_id'],
            'lifecycle_status' => 'in_use',
            'condition' => $data['condition'],
        ]);

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $data['user_id'],
            'assigned_date' => Carbon::now()->toDateString(),
            'condition_on_assignment' => $data['condition'],
            'notes' => $data['notes'] ?? null,
        ]);

        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'asset',
            'title' => 'Penugasan Perangkat: ' . $asset->asset_tag,
            'message' => Auth::user()->name . " menugaskan perangkat {$asset->brand} {$asset->model} ({$asset->asset_tag}) kepada Anda.",
            'action_url' => route('resources.assets'),
            'level' => 'info',
        ]);

        AuditLogger::log('assign', 'Asset', $asset->id, null, ['user_id' => $data['user_id']], "Asset {$asset->asset_tag} assigned to user #{$data['user_id']}");
        return back()->with('success', "Asset {$asset->asset_tag} assigned.");
    }

    public function returnAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'condition' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $latestAssignment = $asset->assignments()->whereNull('returned_date')->first();
        if ($latestAssignment) {
            $latestAssignment->update([
                'returned_date' => Carbon::now()->toDateString(),
                'condition_on_return' => $data['condition'],
                'notes' => $data['notes'] ?? $latestAssignment->notes,
            ]);
        }

        $asset->update([
            'current_holder_id' => null,
            'lifecycle_status' => 'available',
            'condition' => $data['condition'],
        ]);

        AuditLogger::log('update', 'Asset', $asset->id, null, ['lifecycle_status' => 'available'], "Asset {$asset->asset_tag} returned to inventory");
        return back()->with('success', "Asset {$asset->asset_tag} returned to available pool.");
    }

    public function logMaintenance(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'maintenance_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'technician_vendor' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        DB::transaction(function () use ($data, $asset) {
            $expenseId = null;
            if ($data['cost'] > 0 && $data['status'] === 'completed') {
                $account = FinancialAccount::first();
                $expense = Expense::create([
                    'expense_number' => 'EXP-MNT-' . strtoupper(uniqid()),
                    'date' => Carbon::now()->toDateString(),
                    'category' => 'equipment',
                    'amount' => $data['cost'],
                    'account_id' => $account->id,
                    'vendor' => $data['technician_vendor'] ?? 'Maintenance Vendor',
                    'notes' => "Maintenance for asset {$asset->asset_tag} ({$data['maintenance_type']})",
                ]);
                $account->decrement('balance', $data['cost']);
                $expenseId = $expense->id;
            }

            $maint = AssetMaintenance::create(array_merge($data, [
                'asset_id' => $asset->id,
                'completed_date' => $data['status'] === 'completed' ? Carbon::now()->toDateString() : null,
                'expense_id' => $expenseId,
            ]));

            if ($data['status'] === 'in_progress') {
                $asset->update(['lifecycle_status' => 'maintenance']);
            } elseif ($data['status'] === 'completed') {
                $asset->update(['lifecycle_status' => $asset->current_holder_id ? 'in_use' : 'available']);
                $asset->recalculateCosts();
            }

            AuditLogger::log('create', 'AssetMaintenance', $maint->id, null, $maint->toArray(), "Maintenance logged for asset {$asset->asset_tag}");
        });

        return back()->with('success', 'Maintenance record logged.');
    }

    public function infrastructure()
    {
        $infra = Infrastructure::with('resource')->orderBy('type')->paginate(20);
        $projects = Project::where('status', 'active')->get();
        return view('resources.infrastructure', compact('infra', 'projects'));
    }

    public function storeInfrastructure(Request $request)
    {
        foreach (['hostname', 'ip_address', 'os', 'specs', 'monthly_cost', 'start_date', 'expiry_date', 'next_billing_date', 'encrypted_credentials'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'type' => 'required|in:vps,hosting,domain,ssl,cloud,other',
            'provider' => 'required|string',
            'name' => 'required|string',
            'hostname' => 'nullable|string',
            'ip_address' => 'nullable|string',
            'os' => 'nullable|string',
            'specs' => 'nullable|string',
            'environment' => 'required|in:development,staging,production',
            'monthly_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'next_billing_date' => 'nullable|date',
            'encrypted_credentials' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $cost = $data['monthly_cost'] ?? 0;
            $resource = Resource::create([
                'resource_code' => 'RES-INF-' . strtoupper(uniqid()),
                'name' => $data['name'],
                'category' => 'infrastructure',
                'cost' => $cost,
                'status' => 'active',
                'expiry_date' => $data['expiry_date'] ?? null,
                'relevant_date' => $data['next_billing_date'] ?? null,
            ]);

            $infra = Infrastructure::create(array_merge($data, [
                'resource_id' => $resource->id,
            ]));

            AuditLogger::log('create', 'Infrastructure', $infra->id, null, ['type' => $infra->type, 'name' => $infra->name], "New infrastructure {$infra->name} registered");
        });

        return back()->with('success', 'Infrastructure resource registered.');
    }

    public function revealCredential(Request $request, Infrastructure $infrastructure)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Restricted credential access.');
        }

        // Section AF: Credential access MUST be audited!
        AuditLogger::log('credential_access', 'Infrastructure', $infrastructure->id, null, null, "Super Admin accessed decrypted credentials for {$infrastructure->name}");

        return response()->json([
            'credentials' => $infrastructure->encrypted_credentials ?? 'No credentials stored.',
        ]);
    }

    public function subscriptions()
    {
        $subscriptions = Subscription::with('resource')->orderBy('next_billing_date')->get();
        return view('resources.subscriptions', compact('subscriptions'));
    }

    public function storeSubscription(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|string',
            'plan_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,quarterly',
            'next_billing_date' => 'required|date',
            'max_users' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data) {
            $resource = Resource::create([
                'resource_code' => 'RES-SUB-' . strtoupper(uniqid()),
                'name' => "{$data['provider']} ({$data['plan_name']})",
                'category' => 'subscription',
                'cost' => $data['cost'],
                'status' => 'active',
                'relevant_date' => $data['next_billing_date'],
            ]);

            $sub = Subscription::create(array_merge($data, [
                'resource_id' => $resource->id,
            ]));

            AuditLogger::log('create', 'Subscription', $sub->id, null, $sub->toArray(), "Subscription registered: {$sub->provider}");
        });

        return back()->with('success', 'Subscription added.');
    }

    public function inventory()
    {
        $items = InventoryItem::with('transactions.user')->paginate(20);
        return view('resources.inventory', compact('items'));
    }

    public function storeInventoryTransaction(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'transaction_type' => 'required|in:stock_in,stock_out,adjustment,transfer,usage,return',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $qty = $data['quantity'];
        if (in_array($data['transaction_type'], ['stock_out', 'usage'])) {
            if ($item->current_stock < $qty) {
                return back()->withErrors(['quantity' => 'Insufficient stock on hand.']);
            }
            $item->decrement('current_stock', $qty);
        } else {
            $item->increment('current_stock', $qty);
        }

        $item->updateStockStatus();

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => Auth::id(),
            'transaction_type' => $data['transaction_type'],
            'quantity' => $qty,
            'balance_after' => $item->current_stock,
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLogger::log('create', 'InventoryTransaction', $item->id, null, ['type' => $data['transaction_type'], 'qty' => $qty, 'balance' => $item->current_stock], "Stock adjustment for {$item->item_name}");

        return back()->with('success', 'Stock movement recorded successfully.');
    }

    public function storeInventoryItem(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'sku' => 'required|string|unique:inventory_items,sku',
            'item_name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit' => 'required|string',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'location' => 'nullable|string',
            'supplier' => 'nullable|string',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::create($data);
        $item->updateStockStatus();

        AuditLogger::log('create', 'InventoryItem', $item->id, null, $item->toArray(), "Inventory SKU {$item->sku} registered");
        return back()->with('success', "Inventory item {$item->item_name} registered.");
    }

    public function contracts()
    {
        $contracts = Contract::with('resource')->orderBy('expiry_date')->get();
        $projects = Project::orderBy('name')->get();
        return view('resources.contracts', compact('contracts', 'projects'));
    }

    public function storeContract(Request $request)
    {
        if (!$request->filled('project_id')) {
            $request->merge(['project_id' => null]);
        }

        $data = $request->validate([
            'contract_number' => 'required|string|unique:contracts,contract_number',
            'party_name' => 'required|string',
            'party_type' => 'required|in:client,vendor,partner',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
            'contract_value' => 'required|numeric|min:0',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        DB::transaction(function () use ($data) {
            $resource = Resource::create([
                'resource_code' => 'RES-CTR-' . strtoupper(uniqid()),
                'name' => $data['contract_number'] . ' — ' . $data['party_name'],
                'category' => 'contract',
                'project_id' => $data['project_id'] ?? null,
                'cost' => $data['contract_value'],
                'status' => 'active',
                'expiry_date' => $data['expiry_date'],
            ]);

            $contract = Contract::create(array_merge($data, [
                'resource_id' => $resource->id,
                'status' => 'active',
            ]));

            AuditLogger::log('create', 'Contract', $contract->id, null, $contract->toArray(), "Contract {$contract->contract_number} registered");
        });

        return back()->with('success', 'Contract registered and linked to the Schedule Engine.');
    }

    public function accounts()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Company accounts are restricted.');
        }

        $accounts = CompanyAccount::with('resource')->orderBy('platform')->get();
        return view('resources.accounts', compact('accounts'));
    }

    public function storeAccount(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'platform' => 'required|string',
            'account_identifier' => 'required|string',
            'encrypted_credentials' => 'required|string',
            'two_factor_status' => 'nullable|boolean',
            'recovery_method' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $resource = Resource::create([
                'resource_code' => 'RES-ACC-' . strtoupper(uniqid()),
                'name' => $data['platform'] . ' — ' . $data['account_identifier'],
                'category' => 'account',
                'owner_id' => Auth::id(),
                'status' => 'active',
            ]);

            $account = CompanyAccount::create([
                'resource_id' => $resource->id,
                'platform' => $data['platform'],
                'account_identifier' => $data['account_identifier'],
                'encrypted_credentials' => $data['encrypted_credentials'],
                'two_factor_status' => $request->boolean('two_factor_status'),
                'recovery_method' => $data['recovery_method'] ?? null,
                'status' => 'active',
            ]);

            AuditLogger::log('create', 'CompanyAccount', $account->id, null, ['platform' => $account->platform], "Company account {$account->platform} registered");
        });

        return back()->with('success', 'Company account stored with encrypted credentials.');
    }

    public function licenses()
    {
        $licenses = License::with('resource', 'user')->orderBy('expiry_date')->get();
        $users = User::where('status', 'active')->get();
        return view('resources.licenses', compact('licenses', 'users'));
    }

    public function storeLicense(Request $request)
    {
        $data = $request->validate([
            'software_name' => 'required|string',
            'license_key_encrypted' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'device_name' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $resource = Resource::create([
                'resource_code' => 'RES-LIC-' . strtoupper(uniqid()),
                'name' => $data['software_name'],
                'category' => 'license',
                'cost' => $data['cost'],
                'status' => 'active',
                'expiry_date' => $data['expiry_date'] ?? null,
            ]);

            $license = License::create(array_merge($data, [
                'resource_id' => $resource->id,
                'status' => 'active',
            ]));

            AuditLogger::log('create', 'License', $license->id, null, ['software' => $license->software_name], "License {$license->software_name} registered");
        });

        return back()->with('success', 'License registered. Expiry is owned by the license record and read by the Schedule Engine.');
    }
}
