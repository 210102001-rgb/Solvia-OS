@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ itemModal: false, txModal: false, selectedItem: null, selectedItemName: '' }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Inventory
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $items->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Consumables and stock items — cables, components, supplies — with low-stock alerts</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <button @click="itemModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white shadow-md shadow-emerald-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Item
        </button>
        @endif
    </div>

    <!-- STOCK SUMMARY -->
    @php
        $lowStock = $items->getCollection()->where('status', 'low_stock');
        $outOfStock = $items->getCollection()->where('status', 'out_of_stock');
    @endphp
    @if($lowStock->count() > 0 || $outOfStock->count() > 0)
    <div class="p-4 rounded-2xl bg-amber-950/30 border border-amber-500/30 flex items-center gap-3 text-xs">
        <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <p class="text-amber-200">
            <strong>Stock Alert:</strong>
            @if($outOfStock->count() > 0)<span class="text-rose-400 font-bold">{{ $outOfStock->count() }} item(s) out of stock</span>@endif
            @if($lowStock->count() > 0 && $outOfStock->count() > 0) &nbsp;|&nbsp; @endif
            @if($lowStock->count() > 0)<span class="text-amber-400 font-bold">{{ $lowStock->count() }} item(s) low stock</span>@endif
            — consider raising a Purchase Request.
        </p>
    </div>
    @endif

    <!-- INVENTORY TABLE — Adaptive (BK) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">SKU / Item</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-400 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-400 uppercase tracking-wider">Min Stock</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Unit Cost</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($items as $item)
                    @php
                        $statusColor = match($item->status) {
                            'in_stock' => 'bg-emerald-500/20 text-emerald-300',
                            'low_stock' => 'bg-amber-500/20 text-amber-300',
                            'out_of_stock' => 'bg-rose-500/20 text-rose-300',
                            default => 'bg-slate-800 text-slate-400',
                        };
                        $stockPct = $item->minimum_stock > 0 ? min(round($item->current_stock / max($item->minimum_stock * 2, 1) * 100), 100) : 100;
                        $barColor = $item->status === 'out_of_stock' ? 'bg-rose-500' : ($item->status === 'low_stock' ? 'bg-amber-500' : 'bg-emerald-500');
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition {{ $item->status !== 'in_stock' ? 'bg-slate-900' : '' }}">
                        <td class="px-5 py-3.5">
                            <span class="text-[9px] font-mono text-slate-500 block">{{ $item->sku }}</span>
                            <span class="font-semibold text-white">{{ $item->item_name }}</span>
                            @if($item->supplier)<span class="text-[10px] text-slate-500 block">{{ $item->supplier }}</span>@endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-300 capitalize">{{ $item->category }}</td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-mono font-black text-white text-sm">{{ $item->current_stock }}</span>
                                <span class="text-[9px] text-slate-500">{{ $item->unit }}</span>
                                <div class="w-16 h-1.5 rounded-full bg-slate-950 overflow-hidden">
                                    <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ $stockPct }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-mono text-slate-400">{{ $item->minimum_stock }}</td>
                        <td class="px-4 py-3.5 text-slate-400">{{ $item->location ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-200">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $statusColor }}">{{ str_replace('_', ' ', $item->status) }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <button
                                @click="selectedItem = {{ $item->id }}; selectedItemName = '{{ addslashes($item->item_name) }}'; txModal = true"
                                class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[10px] transition">
                                Move Stock
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">No inventory items registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-800 px-5 py-3">
            {{ $items->links() }}
        </div>
    </div>

    <!-- MODAL: REGISTER INVENTORY ITEM -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="itemModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="itemModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="itemModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="itemModal = false">
            <div x-show="itemModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Inventory Item</h3>
                    <button @click="itemModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('resources.inventory.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">SKU *</label>
                            <input type="text" name="sku" required placeholder="e.g. CABLE-LAN-001" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Item Name *</label>
                            <input type="text" name="item_name" required placeholder="e.g. LAN Cable Cat6" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <input type="text" name="category" required placeholder="e.g. Networking, Hardware" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Unit *</label>
                            <input type="text" name="unit" required placeholder="e.g. pcs, meter, roll" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Initial Stock *</label>
                            <input type="number" name="current_stock" min="0" value="0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Min. Stock *</label>
                            <input type="number" name="minimum_stock" min="0" value="5" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Unit Cost (Rp) *</label>
                            <input type="number" name="unit_cost" min="0" value="0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Storage Location</label>
                            <input type="text" name="location" placeholder="e.g. Rack B, Shelf 2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Supplier</label>
                            <input type="text" name="supplier" placeholder="e.g. PT Toko Kabel Jaya" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="itemModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/20">Register Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: STOCK TRANSACTION -->
    <div x-cloak x-show="txModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="txModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="txModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="txModal = false">
            <div x-show="txModal" x-transition @click.stop class="relative z-20 w-full max-w-sm rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-sm text-white">Record Stock Movement</h3>
                    <button @click="txModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <p class="text-xs text-slate-400 mb-3" x-text="'Item: ' + selectedItemName"></p>
                <form :action="'/resources/inventory/' + selectedItem + '/transaction'" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Transaction Type *</label>
                        <select name="transaction_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                            <option value="stock_in">Stock In (+)</option>
                            <option value="stock_out">Stock Out (-)</option>
                            <option value="usage">Usage (-)</option>
                            <option value="return">Return (+)</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Quantity *</label>
                        <input type="number" name="quantity" min="1" required placeholder="Enter quantity" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Reason for stock movement" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="txModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs">Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
