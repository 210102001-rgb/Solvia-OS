@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ licenseModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Software Licenses
                <span class="text-xs bg-orange-500/20 text-orange-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $licenses->count() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Per-device and per-user software licenses — expiry integrated with Schedule Engine</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <button @click="licenseModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-xs font-semibold text-white shadow-md shadow-orange-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register License
        </button>
        @endif
    </div>

    <!-- EXPIRY SUMMARY -->
    @php
        $expiring30 = $licenses->filter(fn($l) => $l->expiry_date && now()->diffInDays($l->expiry_date, false) <= 30 && now()->diffInDays($l->expiry_date, false) >= 0);
        $expired = $licenses->filter(fn($l) => $l->expiry_date && now()->diffInDays($l->expiry_date, false) < 0);
        $perpetual = $licenses->whereNull('expiry_date');
        $totalCost = $licenses->sum('cost');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Licenses</p>
            <p class="text-2xl font-black text-white font-mono mt-1">{{ $licenses->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Cost</p>
            <p class="text-2xl font-black text-orange-400 font-mono mt-1">Rp {{ number_format($totalCost / 1000000, 1) }}M</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border {{ $expiring30->count() > 0 ? 'border-amber-500/30 bg-amber-950/10' : 'border-slate-800' }}">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Expiring (30d)</p>
            <p class="text-2xl font-black {{ $expiring30->count() > 0 ? 'text-amber-400' : 'text-slate-400' }} font-mono mt-1">{{ $expiring30->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border {{ $expired->count() > 0 ? 'border-rose-500/30 bg-rose-950/10' : 'border-slate-800' }}">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Expired</p>
            <p class="text-2xl font-black {{ $expired->count() > 0 ? 'text-rose-400' : 'text-slate-400' }} font-mono mt-1">{{ $expired->count() }}</p>
        </div>
    </div>

    <!-- LICENSE TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Software</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Assigned User / Device</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Purchased</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Expiry</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Cost</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($licenses->sortBy('expiry_date') as $license)
                    @php
                        $expiryDays = $license->expiry_date ? now()->diffInDays($license->expiry_date, false) : null;
                        $expiryColor = $expiryDays === null ? 'text-slate-500' : ($expiryDays < 0 ? 'text-rose-400 font-bold' : ($expiryDays < 30 ? 'text-amber-400 font-bold' : 'text-slate-400'));
                        $statusColor = match($license->status) {
                            'active' => 'bg-emerald-500/20 text-emerald-300',
                            'expired' => 'bg-rose-500/20 text-rose-300',
                            'cancelled' => 'bg-slate-800 text-slate-400',
                            default => 'bg-slate-800 text-slate-300',
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-white">{{ $license->software_name }}</p>
                            <p class="text-[10px] font-mono text-slate-500 mt-0.5">Key: ••••••••••••••••</p>
                        </td>
                        <td class="px-4 py-3.5 text-slate-300">
                            {{ $license->user ? $license->user->name : '—' }}
                            @if($license->device_name)<span class="block text-[10px] text-slate-500">{{ $license->device_name }}</span>@endif
                        </td>
                        <td class="px-4 py-3.5 font-mono text-slate-400">
                            {{ $license->purchase_date ? $license->purchase_date->format('d M Y') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 font-mono {{ $expiryColor }}">
                            {{ $license->expiry_date ? $license->expiry_date->format('d M Y') : 'Perpetual' }}
                            @if($expiryDays !== null && $expiryDays >= 0 && $expiryDays <= 30)
                                <span class="text-[9px] block">{{ $expiryDays }}d left</span>
                            @elseif($expiryDays !== null && $expiryDays < 0)
                                <span class="text-[9px] block">{{ abs($expiryDays) }}d ago</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-200">Rp {{ number_format($license->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $statusColor }}">{{ $license->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">No licenses registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- REGISTER LICENSE MODAL -->
    @if(Auth::user()->isSuperAdmin())
    <div x-cloak x-show="licenseModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="licenseModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="licenseModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="licenseModal = false">
            <div x-show="licenseModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Register Software License</h3>
                    <button @click="licenseModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('resources.licenses.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Software Name *</label>
                        <input type="text" name="software_name" required placeholder="e.g. Adobe Premiere Pro, Windows 11 Pro" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">License Key (stored encrypted) *</label>
                        <input type="text" name="license_key_encrypted" required placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-orange-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Assigned User</label>
                            <select name="user_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-orange-500">
                                <option value="">Not assigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Device Name</label>
                            <input type="text" name="device_name" placeholder="e.g. LAPTOP-001, Design PC" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-orange-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Purchase Date</label>
                            <input type="date" name="purchase_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-orange-500">
                            <p class="text-[9px] text-slate-500 mt-0.5">Leave blank = perpetual</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Cost (Rp) *</label>
                            <input type="number" name="cost" min="0" value="0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-orange-500">
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="licenseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-lg shadow-orange-600/20">Register License</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
