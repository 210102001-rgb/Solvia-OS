@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Biaya Berulang (Recurring Cost Engine)
                <span class="text-xs bg-cyan-500/20 text-cyan-300 font-bold px-2 py-0.5 rounded-full uppercase">MRC & ARC</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Estimasi komitmen pengeluaran rutin dari infrastruktur server, VPS, domain, dan SaaS subscriptions.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300">
                Resource Registry Synced
            </span>
        </div>
    </div>

    <!-- MRC / ARC METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-cyan-950/20 to-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-cyan-400 block tracking-wider">Monthly Recurring Cost (MRC)</span>
            <p class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($recurring['mrc'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-400 block mt-1">Beban komitmen per bulan</span>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950/20 to-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-indigo-400 block tracking-wider">Annual Recurring Cost (ARC)</span>
            <p class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($recurring['arc'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-400 block mt-1">Proyeksi biaya per tahun</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Server & VPS Bulanan</span>
            <p class="text-xl font-bold text-sky-400 font-mono mt-1">Rp {{ number_format($recurring['vps_infrastructure_monthly'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-500 block mt-1">{{ $infrastructures->count() }} Unit Infrastruktur</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">SaaS & Tools Bulanan</span>
            <p class="text-xl font-bold text-purple-400 font-mono mt-1">Rp {{ number_format($recurring['subscriptions_monthly'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-500 block mt-1">{{ $subscriptions->count() }} Langganan Aktif</span>
        </div>
    </div>

    <!-- INFRASTRUCTURES LIST -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Server & Cloud Infrastructure (Resource Registry)</h3>
            <span class="text-xs font-mono text-slate-400">{{ $infrastructures->count() }} Active Nodes</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Nama Server / Host</th>
                        <th class="py-3 px-4">Provider</th>
                        <th class="py-3 px-4">IP / Domain</th>
                        <th class="py-3 px-4 text-right">Biaya Bulanan</th>
                        <th class="py-3 px-4 text-right">Biaya Tahunan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($infrastructures as $infra)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-bold text-white">{{ $infra->name }}</td>
                        <td class="py-3 px-4 text-slate-300">{{ $infra->provider }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400">{{ $infra->ip_address ?? '—' }}</td>
                        <td class="py-3 px-4 text-right font-mono text-cyan-300 font-bold">
                            Rp {{ number_format($infra->monthly_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-400">
                            Rp {{ number_format($infra->yearly_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">
                                {{ $infra->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500">
                            Belum ada server atau infrastruktur yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SUBSCRIPTIONS LIST -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Software & Tool Subscriptions (SaaS Registry)</h3>
            <span class="text-xs font-mono text-slate-400">{{ $subscriptions->count() }} Subscriptions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Nama Layanan / Aplikasi</th>
                        <th class="py-3 px-4">Vendor</th>
                        <th class="py-3 px-4">Siklus Tagihan</th>
                        <th class="py-3 px-4 text-right">Biaya Nominal</th>
                        <th class="py-3 px-4">Jatuh Tempo Perpanjangan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($subscriptions as $sub)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-bold text-white">{{ $sub->name }}</td>
                        <td class="py-3 px-4 text-slate-300">{{ $sub->vendor ?? '—' }}</td>
                        <td class="py-3 px-4 uppercase font-mono text-[10px] text-purple-300">
                            {{ $sub->billing_cycle }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-purple-400 font-bold">
                            Rp {{ number_format($sub->cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400">
                            {{ $sub->next_billing_date ? \Carbon\Carbon::parse($sub->next_billing_date)->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">
                                {{ $sub->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500">
                            Belum ada software subscriptions yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
