@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ infraModal: false, credentialModal: false, selectedInfra: null, credentialData: null, loadingCred: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Infrastructure
                <span class="text-xs bg-cyan-500/20 text-cyan-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $infra->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">VPS, Hosting, Domains, SSL, Cloud — lifecycle-tracked with Schedule Engine integration</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <button @click="infraModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-xs font-semibold text-white shadow-md shadow-cyan-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Infrastructure
        </button>
        @endif
    </div>

    <!-- TYPE FILTER PILLS -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('resources.infrastructure') }}" class="px-3 py-1.5 rounded-xl {{ !request('type') ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }} transition">All</a>
        @foreach(['vps','hosting','domain','ssl','cloud','other'] as $type)
        <a href="{{ route('resources.infrastructure', ['type' => $type]) }}" class="px-3 py-1.5 rounded-xl {{ request('type') === $type ? 'bg-cyan-600 text-white font-bold' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }} transition capitalize">{{ strtoupper($type) }}</a>
        @endforeach
    </div>

    <!-- INFRASTRUCTURE LIST — Mobile-Adaptive Cards (Section BS) -->
    <div class="space-y-3">
        @forelse($infra as $item)
        @php
            $billingDays = $item->next_billing_date ? now()->diffInDays($item->next_billing_date, false) : null;
            $expiryDays = $item->expiry_date ? now()->diffInDays($item->expiry_date, false) : null;
            $urgentDays = min($billingDays ?? 9999, $expiryDays ?? 9999);
            $urgencyColor = $urgentDays < 0 ? 'border-rose-500/40 bg-rose-950/20' : ($urgentDays < 14 ? 'border-amber-500/30 bg-amber-950/10' : ($urgentDays < 30 ? 'border-indigo-500/20' : 'border-slate-800'));
            $envColor = match($item->environment) {
                'production' => 'bg-emerald-500/20 text-emerald-300',
                'staging' => 'bg-amber-500/20 text-amber-300',
                default => 'bg-slate-800 text-slate-300',
            };
            $typeColor = match($item->type) {
                'vps' => 'bg-cyan-500/20 text-cyan-300',
                'domain' => 'bg-purple-500/20 text-purple-300',
                'ssl' => 'bg-indigo-500/20 text-indigo-300',
                'hosting' => 'bg-blue-500/20 text-blue-300',
                default => 'bg-slate-800 text-slate-300',
            };
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 {{ $urgencyColor }} border transition shadow-md text-xs">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $typeColor }}">{{ strtoupper($item->type) }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $envColor }}">{{ $item->environment }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $item->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ $item->status }}</span>
                        @if($urgentDays !== null && $urgentDays < 30 && $urgentDays >= 0)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500/30 text-amber-200">{{ $urgentDays }}d to renewal</span>
                        @elseif($urgentDays !== null && $urgentDays < 0)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-rose-500/30 text-rose-200">Overdue</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-white text-sm">{{ $item->name }}</h3>
                    <p class="text-slate-400 mt-0.5">{{ $item->provider }}
                        @if($item->hostname) • <span class="font-mono text-[10px] text-slate-500">{{ $item->hostname }}</span>@endif
                        @if($item->ip_address) • <span class="font-mono text-[10px] text-slate-500">{{ $item->ip_address }}</span>@endif
                    </p>
                    @if($item->purpose)<p class="text-slate-500 text-[10px] mt-0.5 truncate">{{ $item->purpose }}</p>@endif
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-2 gap-3 text-center sm:text-right sm:min-w-[160px]">
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Monthly</p>
                        <p class="font-mono text-slate-200 font-bold mt-0.5">Rp {{ number_format($item->monthly_cost ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Next Billing</p>
                        <p class="font-mono mt-0.5 {{ $billingDays !== null && $billingDays < 14 ? 'text-amber-400 font-bold' : 'text-slate-400' }}">
                            {{ $item->next_billing_date ? $item->next_billing_date->format('d M Y') : '—' }}
                        </p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Expiry</p>
                        <p class="font-mono mt-0.5 {{ $expiryDays !== null && $expiryDays < 30 ? ($expiryDays < 0 ? 'text-rose-400 font-bold' : 'text-amber-400 font-bold') : 'text-slate-400' }}">
                            {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            @if(Auth::user()->isSuperAdmin())
            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center gap-2">
                @if($item->encrypted_credentials)
                <button
                    @click="selectedInfra = {{ $item->id }}; credentialModal = true; credentialData = null; loadingCred = true;
                        fetch('/resources/infrastructure/{{ $item->id }}/reveal', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}})
                        .then(r=>r.json()).then(d=>{ credentialData = d.credentials; loadingCred = false; });"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold text-xs transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Reveal Credentials
                </button>
                @endif
                <span class="text-[10px] text-slate-500">Auto-renewal: <span class="{{ $item->auto_renewal ? 'text-emerald-400' : 'text-slate-400' }}">{{ $item->auto_renewal ? 'On' : 'Off' }}</span></span>
            </div>
            @endif
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No infrastructure registered. Add VPS, domains, SSL, and hosting resources above.
        </div>
        @endforelse
    </div>

    <div>{{ $infra->links() }}</div>

    <!-- CREDENTIAL VIEWER MODAL -->
    <div x-cloak x-show="credentialModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="credentialModal" x-transition.opacity class="fixed inset-0 bg-slate-950/90 backdrop-blur-sm" @click="credentialModal = false; credentialData = null;"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="credentialModal = false; credentialData = null;">
            <div x-show="credentialModal" x-transition @click.stop class="relative z-20 w-full max-w-md rounded-2xl bg-slate-900 border border-amber-500/30 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-sm text-amber-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Restricted Access — Credentials
                    </h3>
                    <button @click="credentialModal = false; credentialData = null;" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <div class="p-3 rounded-xl bg-slate-950 border border-amber-500/20 font-mono text-xs text-amber-200 whitespace-pre-wrap min-h-[80px]">
                    <template x-if="loadingCred"><span class="text-slate-400">Decrypting...</span></template>
                    <template x-if="!loadingCred && credentialData"><span x-text="credentialData"></span></template>
                </div>
                <p class="text-[10px] text-slate-500 mt-3">This access has been audit-logged. Handle with care.</p>
            </div>
        </div>
    </div>

    <!-- REGISTER INFRASTRUCTURE MODAL -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="infraModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="infraModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="infraModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="infraModal = false">
            <div x-show="infraModal" x-transition @click.stop class="relative z-20 w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Infrastructure Resource</h3>
                    <button @click="infraModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('resources.infrastructure.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Type *</label>
                            <select name="type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                                <option value="vps">VPS</option>
                                <option value="hosting">Hosting</option>
                                <option value="domain">Domain</option>
                                <option value="ssl">SSL Certificate</option>
                                <option value="cloud">Cloud Service</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Environment *</label>
                            <select name="environment" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                                <option value="production">Production</option>
                                <option value="staging">Staging</option>
                                <option value="development">Development</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Provider *</label>
                            <input type="text" name="provider" required placeholder="e.g. DigitalOcean, Namecheap" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Name *</label>
                            <input type="text" name="name" required placeholder="e.g. api.solvia.id, prod-vps-01" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Hostname / Domain</label>
                            <input type="text" name="hostname" placeholder="e.g. 192.168.1.1" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">IP Address</label>
                            <input type="text" name="ip_address" placeholder="e.g. 142.93.x.x" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Monthly Cost (Rp)</label>
                            <input type="number" name="monthly_cost" min="0" placeholder="e.g. 150000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Next Billing</label>
                            <input type="date" name="next_billing_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Purpose / Notes</label>
                        <input type="text" name="specs" placeholder="e.g. Main production API server for all client projects" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Credentials (stored encrypted)</label>
                        <textarea name="encrypted_credentials" rows="2" placeholder="SSH key, login credentials, API key — stored securely" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-cyan-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="infraModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-600/20">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
