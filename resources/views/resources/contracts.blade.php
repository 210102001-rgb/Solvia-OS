@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ contractModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Contracts
                <span class="text-xs bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $contracts->count() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Client, vendor, and partner agreements — expiry-tracked via Schedule Engine</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <button @click="contractModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-semibold text-white shadow-md shadow-rose-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Contract
        </button>
        @endif
    </div>

    <!-- CONTRACT SUMMARY -->
    @php
        $expiring30 = $contracts->filter(fn($c) => $c->expiry_date && now()->diffInDays($c->expiry_date, false) <= 30 && now()->diffInDays($c->expiry_date, false) >= 0);
        $expired = $contracts->filter(fn($c) => $c->expiry_date && now()->diffInDays($c->expiry_date, false) < 0 && $c->status === 'active');
        $totalValue = $contracts->sum('contract_value');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Contracts</p>
            <p class="text-2xl font-black text-white font-mono mt-1">{{ $contracts->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Value</p>
            <p class="text-2xl font-black text-indigo-400 font-mono mt-1">Rp {{ number_format($totalValue / 1000000, 0) }}M</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border {{ $expiring30->count() > 0 ? 'border-amber-500/30 bg-amber-950/10' : 'border-slate-800' }}">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Expiring (30d)</p>
            <p class="text-2xl font-black {{ $expiring30->count() > 0 ? 'text-amber-400' : 'text-slate-400' }} font-mono mt-1">{{ $expiring30->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border {{ $expired->count() > 0 ? 'border-rose-500/30 bg-rose-950/10' : 'border-slate-800' }}">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Expired (Active)</p>
            <p class="text-2xl font-black {{ $expired->count() > 0 ? 'text-rose-400' : 'text-slate-400' }} font-mono mt-1">{{ $expired->count() }}</p>
        </div>
    </div>

    <!-- CONTRACT TABLE — Adaptive (BK) -->
    <div class="space-y-3">
        @forelse($contracts->sortBy('expiry_date') as $contract)
        @php
            $expiryDays = $contract->expiry_date ? now()->diffInDays($contract->expiry_date, false) : null;
            $urgency = $expiryDays !== null ? ($expiryDays < 0 ? 'border-rose-500/40 bg-rose-950/10' : ($expiryDays < 30 ? 'border-amber-500/30' : 'border-slate-800')) : 'border-slate-800';
            $partyColor = match($contract->party_type) {
                'client' => 'bg-indigo-500/20 text-indigo-300',
                'vendor' => 'bg-amber-500/20 text-amber-300',
                'partner' => 'bg-purple-500/20 text-purple-300',
                default => 'bg-slate-800 text-slate-300',
            };
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border {{ $urgency }} transition shadow-md text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1">
                        <span class="font-mono font-bold text-white text-sm">{{ $contract->contract_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $partyColor }}">{{ $contract->party_type }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $contract->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ $contract->status }}</span>
                        @if($expiryDays !== null && $expiryDays >= 0 && $expiryDays <= 30)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500/30 text-amber-200">{{ $expiryDays }}d to expiry</span>
                        @elseif($expiryDays !== null && $expiryDays < 0)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-rose-500/30 text-rose-200">Expired {{ abs($expiryDays) }}d ago</span>
                        @endif
                    </div>
                    <p class="font-semibold text-white">{{ $contract->party_name }}</p>
                    @if($contract->project)<p class="text-indigo-400 text-[10px] mt-0.5">Project: {{ $contract->project->name }}</p>@endif
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-3 gap-4 text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Value</p>
                        <p class="font-mono font-bold text-white mt-0.5">Rp {{ number_format($contract->contract_value / 1000000, 1) }}M</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Start</p>
                        <p class="font-mono text-slate-400 mt-0.5">{{ $contract->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Expiry</p>
                        <p class="font-mono mt-0.5 {{ $expiryDays !== null && $expiryDays < 30 ? ($expiryDays < 0 ? 'text-rose-400 font-bold' : 'text-amber-400 font-bold') : 'text-slate-400' }}">
                            {{ $contract->expiry_date ? $contract->expiry_date->format('d M Y') : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No contracts registered. Add client agreements, vendor contracts, and partnerships above.
        </div>
        @endforelse
    </div>

    <!-- REGISTER CONTRACT MODAL -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="contractModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="contractModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="contractModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="contractModal = false">
            <div x-show="contractModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Contract</h3>
                    <button @click="contractModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('resources.contracts.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Contract Number *</label>
                            <input type="text" name="contract_number" required placeholder="e.g. CTR-2026-001" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Party Type *</label>
                            <select name="party_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="client">Client</option>
                                <option value="vendor">Vendor</option>
                                <option value="partner">Partner</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Party Name *</label>
                        <input type="text" name="party_name" required placeholder="Client or vendor company name" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Expiry Date *</label>
                            <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Contract Value (Rp) *</label>
                            <input type="number" name="contract_value" min="0" required placeholder="e.g. 75000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Related Project</label>
                            <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="">No specific project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="contractModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/20">Register Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
