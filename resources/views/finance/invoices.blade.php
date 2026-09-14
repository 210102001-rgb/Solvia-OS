@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    invoiceModal: false,
    items: [{ description: 'Development Services', quantity: 1, unit_price: 25000000 }],
    addItem() { this.items.push({ description: '', quantity: 1, unit_price: 0 }); },
    removeItem(idx) { if (this.items.length > 1) this.items.splice(idx, 1); },
    get subtotal() {
        return this.items.reduce((sum, it) => sum + (Number(it.quantity) * Number(it.unit_price)), 0);
    }
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Invoices & Billing
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $invoices->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Client accounts receivable with automated payment reconciliation</p>
        </div>

        <button @click="invoiceModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate Invoice
        </button>
    </div>

    <!-- INVOICES TABLE (Adaptive Cards on Mobile - Section BK) -->
    <div class="space-y-3">
        @forelse($invoices as $inv)
            @php
                $statusBadge = match($inv->payment_status) {
                    'paid' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                    'overdue' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                    'sent' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                    default => 'bg-slate-800 text-slate-300 border-slate-700',
                };
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-md">
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-white text-sm">{{ $inv->invoice_number }}</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border {{ $statusBadge }}">
                            {{ $inv->payment_status }}
                        </span>
                    </div>
                    <p class="text-slate-400">
                        Client: <strong class="text-slate-200">{{ $inv->client ? $inv->client->name : 'N/A' }}</strong>
                        @if($inv->project) • Project: <span class="text-indigo-300">{{ $inv->project->name }}</span> @endif
                    </p>
                    <p class="text-[11px] text-slate-500 font-mono">
                        Issued: {{ $inv->issue_date->format('d M Y') }} • Due: {{ $inv->due_date->format('d M Y') }}
                    </p>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Amount</span>
                        <span class="font-mono font-black text-white text-base">
                            Rp {{ number_format($inv->total, 0, ',', '.') }}
                        </span>
                    </div>

                    @if($inv->payment_status !== 'paid')
                        <form action="{{ route('finance.invoices.pay', $inv->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition">
                                Mark as Paid
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                            Paid &check;
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
                No invoices created yet.
            </div>
        @endforelse

        <div class="pt-4">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- MODAL: GENERATE INVOICE -->
    <div x-cloak x-show="invoiceModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="invoiceModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="invoiceModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="invoiceModal = false">
            <div x-show="invoiceModal" x-transition @click.stop class="relative z-20 w-full max-w-2xl rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Generate Client Invoice</h3>
                    <button @click="invoiceModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('finance.invoices.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Invoice Number *</label>
                            <input type="text" name="invoice_number" value="INV-{{ date('Y') }}-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Client *</label>
                            <select name="client_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                                @foreach($clients as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project (Optional)</label>
                            <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                                <option value="">None</option>
                                @foreach($projects as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Issue Date *</label>
                            <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Due Date *</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                        </div>
                    </div>

                    <!-- Line Items Repeater -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px]">Invoice Line Items</label>
                            <button type="button" @click="addItem()" class="text-indigo-400 hover:text-indigo-300 font-bold text-xs">+ Add Line Item</button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 grid grid-cols-12 gap-2 items-center">
                                    <div class="col-span-6">
                                        <input type="text" :name="'items[' + idx + '][description]'" x-model="item.description" required placeholder="Description of service/deliverable" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" :name="'items[' + idx + '][quantity]'" x-model="item.quantity" min="1" required placeholder="Qty" class="w-full px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs font-mono">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" :name="'items[' + idx + '][unit_price]'" x-model="item.unit_price" min="0" required placeholder="Unit Price" class="w-full px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs font-mono">
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button type="button" @click="removeItem(idx)" class="text-rose-400 hover:text-rose-200 font-bold text-sm">&times;</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-2 text-right text-xs">
                            <span class="text-slate-400">Calculated Subtotal: </span>
                            <strong class="text-white font-mono text-sm" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal)"></strong>
                            <input type="hidden" name="subtotal" :value="subtotal">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="invoiceModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg">Save & Dispatch Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
