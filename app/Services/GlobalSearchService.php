<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Infrastructure;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    public function search(string $query, User $user): Collection
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return collect();
        }

        $results = collect();
        $isSuperAdmin = $user->isSuperAdmin();

        // 1. Projects
        $projects = Project::where('name', 'like', "%{$query}%")
            ->orWhere('project_code', 'like', "%{$query}%");
        if (!$isSuperAdmin) {
            $projects->whereHas('members', fn($q) => $q->where('user_id', $user->id));
        }
        foreach ($projects->limit(5)->get() as $p) {
            $results->push([
                'category' => 'Projects',
                'title' => $p->name,
                'subtitle' => "Code: {$p->project_code} • Health: " . strtoupper(str_replace('_', ' ', $p->health)),
                'url' => route('projects.show', $p->id),
                'icon' => 'folder',
            ]);
        }

        // 2. Tasks
        $tasks = Task::with('project')->where('title', 'like', "%{$query}%");
        if (!$isSuperAdmin) {
            $tasks->where('assignee_id', $user->id);
        }
        foreach ($tasks->limit(5)->get() as $t) {
            $results->push([
                'category' => 'Tasks',
                'title' => $t->title,
                'subtitle' => "Project: " . ($t->project ? $t->project->name : 'N/A') . " • Progress: {$t->progress}%",
                'url' => route('projects.show', $t->project_id),
                'icon' => 'check-square',
            ]);
        }

        // 3. Clients (Super Admin or has client.view)
        if ($isSuperAdmin || $user->hasPermission('client.view')) {
            $clients = Client::where('name', 'like', "%{$query}%")
                ->orWhere('client_code', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%");
            foreach ($clients->limit(5)->get() as $c) {
                $results->push([
                    'category' => 'Clients',
                    'title' => $c->name,
                    'subtitle' => "Contact: {$c->contact_person} • {$c->email}",
                    'url' => route('company.clients'),
                    'icon' => 'users',
                ]);
            }
        }

        // 4. Assets
        if ($isSuperAdmin || $user->hasPermission('asset.view')) {
            $assets = Asset::with('resource')->where('asset_tag', 'like', "%{$query}%")
                ->orWhere('brand', 'like', "%{$query}%")
                ->orWhere('model', 'like', "%{$query}%");
            foreach ($assets->limit(5)->get() as $a) {
                $results->push([
                    'category' => 'Assets',
                    'title' => "{$a->brand} {$a->model} ({$a->asset_tag})",
                    'subtitle' => "Status: " . ucfirst($a->lifecycle_status) . " • Condition: " . ucfirst($a->condition),
                    'url' => route('resources.assets'),
                    'icon' => 'cpu',
                ]);
            }
        }

        // 5. Invoices (Super Admin only)
        if ($isSuperAdmin) {
            $invoices = Invoice::where('invoice_number', 'like', "%{$query}%");
            foreach ($invoices->limit(5)->get() as $inv) {
                $results->push([
                    'category' => 'Finance & Invoices',
                    'title' => "Invoice #{$inv->invoice_number}",
                    'subtitle' => "Total: Rp " . number_format($inv->total, 0, ',', '.') . " • Status: " . ucfirst($inv->payment_status),
                    'url' => route('finance.invoices'),
                    'icon' => 'credit-card',
                ]);
            }
        }

        // 6. Infrastructure (Super Admin or Backend Dev)
        if ($isSuperAdmin || $user->hasPermission('infrastructure.view')) {
            $infrastructures = Infrastructure::where('name', 'like', "%{$query}%")
                ->orWhere('hostname', 'like', "%{$query}%")
                ->orWhere('ip_address', 'like', "%{$query}%");
            foreach ($infrastructures->limit(5)->get() as $infra) {
                $results->push([
                    'category' => 'Infrastructure',
                    'title' => "{$infra->name} (" . strtoupper($infra->type) . ")",
                    'subtitle' => "Host: {$infra->hostname} • IP: {$infra->ip_address}",
                    'url' => route('resources.infrastructure'),
                    'icon' => 'server',
                ]);
            }
        }

        // 7. Inventory Items
        if ($isSuperAdmin || $user->hasPermission('inventory.view')) {
            $inventory = InventoryItem::where('item_name', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%");
            foreach ($inventory->limit(5)->get() as $invItem) {
                $results->push([
                    'category' => 'Inventory',
                    'title' => $invItem->item_name,
                    'subtitle' => "Stock: {$invItem->current_stock} {$invItem->unit} • Status: " . ucfirst(str_replace('_', ' ', $invItem->status)),
                    'url' => route('resources.inventory'),
                    'icon' => 'package',
                ]);
            }
        }

        return $results;
    }
}
