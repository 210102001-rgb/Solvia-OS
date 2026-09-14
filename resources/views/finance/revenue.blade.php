@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Revenue Streams & Incomes
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full uppercase">Top-Line Control</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Klasifikasi sumber pendapatan perusahaan: Project, Service Retainer, Product, & Subscription.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.reports.export', ['type' => 'incomes']) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('finance.incomes') }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Kelola Income Manual
            </a>
        </div>
    </div>

    <!-- TOTAL REVENUE BANNER -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-emerald-950/30 to-slate-900 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Recognized Revenue</span>
            <p class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono mt-0.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Prinsip Akuntansi: <span class="text-slate-300 font-semibold">Revenue ≠ Cash In</span> (Pendapatan diakui saat jasa diserahkan / penagihan, bukan semata-mata saat kas diterima).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 font-mono">
                {{ $incomes->total() }} Records
            </span>
        </div>
    </div>

    <!-- CATEGORY REVENUE CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($byCategory as $cat => $total)
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                <span class="font-bold uppercase tracking-wider">{{ $cat ?: 'Uncategorized' }}</span>
                @php
                    $pct = $totalRevenue > 0 ? round(($total / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="font-mono text-emerald-400 font-bold">{{ $pct }}%</span>
            </div>
            <p class="text-lg font-black text-white font-mono">Rp {{ number_format($total, 0, ',', '.') }}</p>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-4 p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">
            Belum ada kategori pendapatan yang tercatat.
        </div>
        @endforelse
    </div>

    <!-- REVENUE TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Riwayat Pengakuan Pendapatan</h3>
            <span class="text-xs text-slate-400">Sinkron dengan Master Ledger</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">No. Income</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Sumber / Client</th>
                        <th class="py-3 px-4">Project</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Rekening Penampung</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($incomes as $inc)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-200">{{ $inc->income_number }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">{{ $inc->date ? $inc->date->format('d M Y') : '-' }}</td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-white block">{{ $inc->source }}</span>
                            @if($inc->client)
                                <span class="text-[11px] text-slate-400 block">{{ $inc->client->company_name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($inc->project)
                                <span class="px-2 py-0.5 text-[10px] rounded bg-slate-800 text-indigo-300 border border-slate-700 font-mono">
                                    {{ Str::limit($inc->project->title, 18) }}
                                </span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 text-[10px] rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                {{ $inc->category }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-300 whitespace-nowrap">
                            {{ $inc->account->account_name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                            + Rp {{ number_format($inc->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-500">
                            Belum ada catatan pendapatan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incomes->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $incomes->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
