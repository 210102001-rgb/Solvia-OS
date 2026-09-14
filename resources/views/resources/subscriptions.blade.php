@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ subModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Subscriptions
                <span class="text-xs bg-purple-500/20 text-purple-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $subscriptions->count() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">SaaS tools, platforms, and recurring services — billing tracked via Schedule Engine</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <button @click="subModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-xs font-semibold text-white shadow-md shadow-purple-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Subscription
        </button>
        @endif
    </div>

    <!-- MONTHLY BURN STRIP -->
    @php
        $monthlyBurn = $subscriptions->sum(fn($s) => $s->billing_cycle === 'monthly' ? $s->cost : ($s->billing_cycle === 'yearly' ? $s->cost / 12 : $s->cost / 3));
        $yearlyBurn = $subscriptions->sum(fn($s) => $s->billing_cycle === 'yearly' ? $s->cost : ($s->billing_cycle === 'monthly' ? $s->cost * 12 : $s->cost * 4));
        $dueIn30 = $subscriptions->filter(fn($s) => $s->next_billing_date && now()->diffInDays($s->next_billing_date, false) <= 30 && now()->diffInDays($s->next_billing_date, false) >= 0);
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">~Monthly Cost</p>
            <p class="text-xl font-black text-purple-400 font-mono mt-1">Rp {{ number_format($monthlyBurn, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">~Yearly Commitment</p>
            <p class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($yearlyBurn, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border {{ $dueIn30->count() > 0 ? 'border-amber-500/30 bg-amber-950/10' : 'border-slate-800' }}">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Due in 30 Days</p>
            <p class="text-xl font-black {{ $dueIn30->count() > 0 ? 'text-amber-400' : 'text-slate-400' }} font-mono mt-1">{{ $dueIn30->count() }}</p>
        </div>
    </div>

    <!-- SUBSCRIPTION CARDS — Mobile Adaptive (Section BS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($subscriptions->sortBy('next_billing_date') as $sub)
        @php
            $billingDays = $sub->next_billing_date ? now()->diffInDays($sub->next_billing_date, false) : null;
            $urgency = $billingDays !== null ? ($billingDays < 0 ? 'border-rose-500/40' : ($billingDays < 7 ? 'border-rose-500/30' : ($billingDays < 30 ? 'border-amber-500/30' : 'border-slate-800'))) : 'border-slate-800';
            $billingColor = $billingDays !== null ? ($billingDays < 0 ? 'text-rose-400 font-bold' : ($billingDays < 30 ? 'text-amber-400 font-bold' : 'text-slate-400')) : 'text-slate-500';
        @endphp
        <div class="p-5 rounded-2xl bg-slate-900/90 border {{ $urgency }} transition shadow-md text-xs">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h3 class="font-bold text-white text-sm">{{ $sub->provider }}</h3>
                    <p class="text-slate-400 text-[11px] mt-0.5">{{ $sub->plan_name }}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ $sub->status }}</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-800 text-slate-300 capitalize">{{ $sub->billing_cycle }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <p class="text-[9px] text-slate-400 uppercase font-bold">Cost</p>
                    <p class="font-mono font-bold text-white mt-0.5">Rp {{ number_format($sub->cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[9px] text-slate-400 uppercase font-bold">Max Users</p>
                    <p class="text-slate-300 mt-0.5">{{ $sub->max_users }} seats</p>
                </div>
                <div class="col-span-2">
                    <p class="text-[9px] text-slate-400 uppercase font-bold">Next Billing</p>
                    <p class="font-mono mt-0.5 {{ $billingColor }}">
                        {{ $sub->next_billing_date ? $sub->next_billing_date->format('d M Y') : '—' }}
                        @if($billingDays !== null && $billingDays >= 0 && $billingDays <= 30)
                            <span class="text-[9px] opacity-80">({{ $billingDays }}d)</span>
                        @elseif($billingDays !== null && $billingDays < 0)
                            <span class="text-[9px]">({{ abs($billingDays) }}d overdue)</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                <span class="text-[10px] text-slate-500">Auto-renew: <span class="{{ $sub->auto_renewal ? 'text-emerald-400' : 'text-slate-400' }}">{{ $sub->auto_renewal ? 'On' : 'Off' }}</span></span>
                @if($billingDays !== null && $billingDays <= 30 && $billingDays >= 0)
                    <span class="text-[10px] text-amber-400 font-bold">⚠ Renewal Soon</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No subscriptions registered. Add Canva, Figma, GitHub, etc. to start tracking recurring costs.
        </div>
        @endforelse
    </div>

    <!-- ADD SUBSCRIPTION MODAL -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="subModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="subModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="subModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="subModal = false">
            <div x-show="subModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Subscription</h3>
                    <button @click="subModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('resources.subscriptions.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Provider / Service *</label>
                            <input type="text" name="provider" required placeholder="e.g. Figma, GitHub, Canva" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Plan Name *</label>
                            <input type="text" name="plan_name" required placeholder="e.g. Professional, Team, Business" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Cost (Rp) *</label>
                            <input type="number" name="cost" min="0" required placeholder="e.g. 180000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Billing Cycle *</label>
                            <select name="billing_cycle" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Next Billing Date *</label>
                            <input type="date" name="next_billing_date" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Max Users / Seats *</label>
                            <input type="number" name="max_users" min="1" value="5" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-purple-500">
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="subModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-lg shadow-purple-600/20">Add Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
