@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ newRequestModal: false, rejectModal: false, rejectId: null }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Purchase Requests
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $purchases->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Submit, review, and approve purchase requests — linked to Finance & Asset Registry</p>
        </div>
        <button @click="newRequestModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Request
        </button>
    </div>

    <!-- STATUS FILTER PILLS -->
    <div class="flex flex-wrap gap-2 text-xs">
        @foreach([''=>'All', 'submitted'=>'Submitted', 'approved'=>'Approved', 'rejected'=>'Rejected', 'purchased'=>'Purchased', 'received'=>'Received'] as $val => $label)
        <a href="{{ route('purchasing.index', $val ? ['status' => $val] : []) }}"
           class="px-3 py-1.5 rounded-xl {{ request('status') === $val || (request('status') === null && $val === '') ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }} transition">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <!-- PURCHASE REQUESTS TABLE — Adaptive (Section BK) -->
    <div class="space-y-3">
        @forelse($purchases as $pr)
        @php
            $statusColor = match($pr->status) {
                'approved'   => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'purchased'  => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                'received'   => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
                'rejected'   => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'registered' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                default      => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            };
            $priorityColor = match($pr->priority) {
                'urgent' => 'bg-rose-500/20 text-rose-300',
                'high'   => 'bg-orange-500/20 text-orange-300',
                'medium' => 'bg-amber-500/20 text-amber-300',
                default  => 'bg-slate-800 text-slate-400',
            };
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <!-- Left: Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1.5">
                        <span class="font-mono font-bold text-white text-sm">{{ $pr->request_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border {{ $statusColor }}">{{ str_replace('_', ' ', $pr->status) }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $priorityColor }}">{{ $pr->priority }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-800 text-slate-300 capitalize">{{ $pr->category }}</span>
                    </div>
                    <p class="font-semibold text-white">{{ $pr->item_name }}</p>
                    <p class="text-slate-400 mt-0.5">
                        Qty: <span class="text-slate-200 font-semibold">{{ $pr->quantity }}</span>
                        &nbsp;•&nbsp; By: <span class="text-slate-200 font-semibold">{{ $pr->requester->name ?? '—' }}</span>
                        &nbsp;•&nbsp; {{ $pr->created_at->format('d M Y') }}
                    </p>
                    @if($pr->reason)
                    <p class="text-slate-500 text-[11px] mt-1 line-clamp-2">{{ $pr->reason }}</p>
                    @endif
                    @if($pr->rejection_reason)
                    <p class="text-rose-400 text-[11px] mt-1">Rejection: {{ $pr->rejection_reason }}</p>
                    @endif
                </div>

                <!-- Right: Cost & Actions -->
                <div class="flex items-center gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                    <div class="text-right">
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Est. Cost</p>
                        <p class="font-mono font-bold text-white text-sm">Rp {{ number_format($pr->estimated_cost, 0, ',', '.') }}</p>
                        @if($pr->actual_cost)
                        <p class="font-mono text-[10px] text-slate-400">Actual: Rp {{ number_format($pr->actual_cost, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                    <div class="flex flex-col gap-1.5">
                        @if($pr->status === 'submitted')
                        <form action="{{ route('purchasing.approve', $pr->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">Approve</button>
                        </form>
                        <button @click="rejectId = {{ $pr->id }}; rejectModal = true"
                            class="px-3 py-1.5 rounded-xl bg-rose-700 hover:bg-rose-600 text-white font-bold text-xs transition">Reject</button>

                        @elseif($pr->status === 'approved')
                        <form action="{{ route('purchasing.purchased', $pr->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition">Mark Purchased</button>
                        </form>

                        @elseif($pr->status === 'purchased')
                        <form action="{{ route('purchasing.receive', $pr->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition">Mark Received</button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No purchase requests found. Submit your first request above.
        </div>
        @endforelse

        <div>{{ $purchases->links() }}</div>
    </div>

    <!-- MODAL: REJECT REQUEST -->
    <div x-cloak x-show="rejectModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="rejectModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="rejectModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="rejectModal = false">
            <div x-show="rejectModal" x-transition @click.stop class="relative z-20 w-full max-w-sm rounded-2xl bg-slate-900 border border-rose-500/30 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <h3 class="font-bold text-sm text-white mb-4">Reject Purchase Request</h3>
                <form :action="'/purchasing/' + rejectId + '/reject'" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rejection Reason *</label>
                        <textarea name="rejection_reason" required rows="3" placeholder="Explain why this request is rejected" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500"></textarea>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: NEW PURCHASE REQUEST -->
    <div x-cloak x-show="newRequestModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="newRequestModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="newRequestModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="newRequestModal = false">
            <div x-show="newRequestModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Submit Purchase Request</h3>
                    <button @click="newRequestModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('purchasing.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Item Name *</label>
                        <input type="text" name="item_name" required placeholder="e.g. Arduino Nano x10, Wacom Tablet" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="asset">Asset</option>
                                <option value="inventory">Inventory</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="subscription">Subscription</option>
                                <option value="operational">Operational</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Quantity *</label>
                            <input type="number" name="quantity" min="1" value="1" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Estimated Cost (Rp) *</label>
                            <input type="number" name="estimated_cost" min="1" required placeholder="e.g. 650000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Reason / Justification *</label>
                        <textarea name="reason" required rows="3" placeholder="Why is this purchase needed? What project or operational need does it serve?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="newRequestModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Submit for Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
