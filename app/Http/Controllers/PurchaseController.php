<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseRequest::with('requester', 'approver', 'asset', 'inventoryItem', 'expense');

        if (!$user->isSuperAdmin()) {
            $query->where('requester_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->orderByDesc('created_at')->paginate(20);
        return view('purchasing.index', compact('purchases'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:asset,inventory,infrastructure,subscription,operational,other',
            'quantity' => 'required|integer|min:1',
            'estimated_cost' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $data['request_number'] = 'PR-' . strtoupper(uniqid());
        $data['requester_id'] = Auth::id();
        $data['status'] = 'submitted';

        $pr = PurchaseRequest::create($data);
        AuditLogger::log('create', 'PurchaseRequest', $pr->id, null, $pr->toArray(), "Purchase request {$pr->request_number} submitted by " . Auth::user()->name);

        return back()->with('success', "Purchase request {$pr->request_number} submitted for Super Admin review.");
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Approvals are restricted to Super Admin.');
        }

        $this->purchaseService->approve($purchaseRequest, Auth::user());
        return back()->with('success', "Purchase request {$purchaseRequest->request_number} approved.");
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Approvals are restricted to Super Admin.');
        }

        $data = $request->validate(['rejection_reason' => 'required|string']);
        $purchaseRequest->update([
            'status' => 'rejected',
            'approver_id' => Auth::id(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        AuditLogger::log('reject', 'PurchaseRequest', $purchaseRequest->id, null, ['status' => 'rejected'], "Purchase request {$purchaseRequest->request_number} rejected.");
        return back()->with('success', "Purchase request rejected.");
    }

    public function markPurchased(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate(['actual_cost' => 'required|numeric|min:0']);
        $this->purchaseService->markPurchased($purchaseRequest, (float) $data['actual_cost']);

        return back()->with('success', "Item marked as purchased.");
    }

    public function receive(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $options = $request->only(['vendor', 'brand', 'model', 'serial_number', 'specs', 'location', 'unit', 'sku']);
        $this->purchaseService->receiveAndRegister($purchaseRequest, $options);

        return back()->with('success', "Goods received! Automatically registered to " . ucfirst($purchaseRequest->category) . " registry and financial expense ledger.");
    }
}
