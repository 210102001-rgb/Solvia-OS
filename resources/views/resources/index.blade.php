@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="pb-4 border-b border-slate-800/80">
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
            Resource Registry
            <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $counts['total'] }}</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Central inventory of everything owned, used, or managed by Solvia.Nova</p>
    </div>

    <!-- RESOURCE CATEGORY CARDS — Quick Nav -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $categories = [
                ['label' => 'Assets', 'count' => $counts['assets'], 'icon' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9a2 2 0 00-2-2h-4M9 21H5a2 2 0 01-2-2V9a2 2 0 012-2h4', 'route' => 'resources.assets', 'color' => 'indigo'],
                ['label' => 'Infrastructure', 'count' => $counts['infrastructure'], 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2', 'route' => 'resources.infrastructure', 'color' => 'cyan'],
                ['label' => 'Subscriptions', 'count' => $counts['subscriptions'], 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'route' => 'resources.subscriptions', 'color' => 'purple'],
                ['label' => 'Accounts', 'count' => $counts['accounts'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'route' => 'resources.accounts', 'color' => 'amber'],
                ['label' => 'Inventory', 'count' => $counts['inventory'], 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'route' => 'resources.inventory', 'color' => 'emerald'],
                ['label' => 'Contracts', 'count' => 0, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'resources.contracts', 'color' => 'rose'],
            ];
        @endphp
        @foreach($categories as $cat)
        <a href="{{ route($cat['route']) }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-{{ $cat['color'] }}-500/40 hover:bg-{{ $cat['color'] }}-950/20 transition group text-center flex flex-col items-center gap-2">
            <svg class="w-6 h-6 text-slate-400 group-hover:text-{{ $cat['color'] }}-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $cat['icon'] }}"/>
            </svg>
            <span class="text-xl font-black text-white font-mono">{{ $cat['count'] }}</span>
            <span class="text-[10px] font-bold text-slate-400 group-hover:text-slate-200 transition uppercase tracking-wider">{{ $cat['label'] }}</span>
        </a>
        @endforeach
    </div>

    <!-- ALL RESOURCES TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">All Resources</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $resources->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Code / Name</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Owner / Responsible</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Project</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Cost</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Expiry</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($resources as $res)
                    @php
                        $catColor = match($res->category) {
                            'asset' => 'bg-indigo-500/20 text-indigo-300',
                            'infrastructure' => 'bg-cyan-500/20 text-cyan-300',
                            'subscription' => 'bg-purple-500/20 text-purple-300',
                            'account' => 'bg-amber-500/20 text-amber-300',
                            'inventory' => 'bg-emerald-500/20 text-emerald-300',
                            'contract' => 'bg-rose-500/20 text-rose-300',
                            'license' => 'bg-orange-500/20 text-orange-300',
                            default => 'bg-slate-800 text-slate-300',
                        };
                        $expiryDays = $res->expiry_date ? now()->diffInDays($res->expiry_date, false) : null;
                        $expiryColor = $expiryDays !== null ? ($expiryDays < 0 ? 'text-rose-400' : ($expiryDays < 30 ? 'text-amber-400' : 'text-slate-400')) : 'text-slate-500';
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3.5">
                            <span class="text-[9px] font-mono text-slate-500 block">{{ $res->resource_code }}</span>
                            <span class="font-semibold text-white">{{ $res->name }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $catColor }}">{{ $res->category }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-300">
                            {{ $res->owner ? $res->owner->name : '—' }}
                            @if($res->responsibleUser && $res->responsibleUser->id !== $res->owner?->id)
                                <span class="block text-[10px] text-slate-500">{{ $res->responsibleUser->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">{{ $res->project ? $res->project->name : '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-200">
                            {{ $res->cost ? 'Rp ' . number_format($res->cost, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 font-mono {{ $expiryColor }}">
                            {{ $res->expiry_date ? $res->expiry_date->format('d M Y') : '—' }}
                            @if($expiryDays !== null && $expiryDays >= 0 && $expiryDays <= 30)
                                <span class="block text-[9px] font-bold text-amber-400">{{ $expiryDays }}d left</span>
                            @elseif($expiryDays !== null && $expiryDays < 0)
                                <span class="block text-[9px] font-bold text-rose-400">Expired</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $res->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                                {{ $res->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">No resources registered yet. Use the category shortcuts above to add resources.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-800 px-5 py-3">
            {{ $resources->links() }}
        </div>
    </div>

</div>
@endsection
