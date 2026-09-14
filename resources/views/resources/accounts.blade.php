@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ accountModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Company Accounts
                <span class="text-xs bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $accounts->count() }}</span>
                <span class="text-xs bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded-full uppercase">Restricted</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Super Admin only — all company platform accounts with 2FA status and encrypted credentials</p>
        </div>
        <button @click="accountModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs font-semibold text-white shadow-md shadow-amber-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Account
        </button>
    </div>

    <!-- 2FA WARNING STRIP -->
    @php $no2fa = $accounts->where('two_factor_status', false); @endphp
    @if($no2fa->count() > 0)
    <div class="p-4 rounded-2xl bg-rose-950/30 border border-rose-500/30 flex items-center gap-3 text-xs">
        <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <p class="text-rose-200">
            <strong>Security Alert:</strong> {{ $no2fa->count() }} account(s) have 2FA disabled.
            Platforms: <strong>{{ $no2fa->pluck('platform')->join(', ') }}</strong>
        </p>
    </div>
    @endif

    <!-- ACCOUNT GRID — Mobile Adaptive -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($accounts as $account)
        @php
            $platformColor = match(strtolower($account->platform)) {
                'github' => 'bg-slate-800 text-slate-200',
                'figma' => 'bg-purple-900/50 text-purple-300',
                'canva' => 'bg-blue-900/50 text-blue-300',
                'google workspace', 'google' => 'bg-red-900/30 text-red-300',
                'digitalocean', 'vps' => 'bg-blue-950/50 text-cyan-300',
                default => 'bg-slate-800/80 text-slate-300',
            };
        @endphp
        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-amber-500/30 transition shadow-md text-xs">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $platformColor }}">{{ $account->platform }}</span>
                    <p class="font-semibold text-white mt-1.5">{{ $account->account_identifier }}</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $account->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }} flex-shrink-0">{{ $account->status }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <p class="text-[9px] text-slate-400 uppercase font-bold">2FA Status</p>
                    <p class="mt-0.5 font-semibold {{ $account->two_factor_status ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $account->two_factor_status ? '✓ Enabled' : '✗ Disabled' }}
                    </p>
                </div>
                <div>
                    <p class="text-[9px] text-slate-400 uppercase font-bold">Recovery</p>
                    <p class="text-slate-300 mt-0.5 truncate">{{ $account->recovery_method ?? '—' }}</p>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                <span class="text-[10px] text-slate-500">Credentials: <span class="text-amber-400 font-semibold">Encrypted</span></span>
                @if(!$account->two_factor_status)
                    <span class="text-[9px] bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded">No 2FA!</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No company accounts registered. Add GitHub, Figma, Google Workspace, etc.
        </div>
        @endforelse
    </div>

    <!-- ADD ACCOUNT MODAL -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="accountModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="accountModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="accountModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="accountModal = false">
            <div x-show="accountModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-amber-500/30 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Company Account</h3>
                    <button @click="accountModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <div class="p-3 rounded-xl bg-amber-950/30 border border-amber-500/20 text-xs text-amber-200 mb-4 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Credentials are stored and access is audit-logged. Only Super Admin can view them.
                </div>
                <form action="{{ route('resources.accounts.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Platform *</label>
                            <input type="text" name="platform" required placeholder="e.g. GitHub, Figma, Canva" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Account / Email *</label>
                            <input type="text" name="account_identifier" required placeholder="e.g. dev@solvia.id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Credentials (Password / API Key / Token) *</label>
                        <textarea name="encrypted_credentials" required rows="2" placeholder="Will be stored securely — encrypted at rest" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-amber-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <input type="checkbox" name="two_factor_status" value="1" id="2fa_enabled" class="rounded">
                            <label for="2fa_enabled" class="font-semibold text-slate-300 cursor-pointer">2FA Enabled</label>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Recovery Method</label>
                            <input type="text" name="recovery_method" placeholder="e.g. Backup email" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="accountModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20">Store Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
