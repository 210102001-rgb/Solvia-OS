@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Pusat Laporan Keuangan & Export (Financial Reports)
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase">Audit & Intelligence</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Laporan Laba Rugi (P&L), Posisi Kas, Analisis Piutang/Hutang, dan Ekspor Data Audit.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.reports.export', ['type' => 'transactions']) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Ledger (CSV)
            </a>
            <a href="{{ route('finance.reports.export', ['type' => 'incomes']) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Revenue (CSV)
            </a>
            <a href="{{ route('finance.reports.export', ['type' => 'expenses']) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Expense (CSV)
            </a>
        </div>
    </div>

    <!-- P&L EXECUTIVE CARD -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- LABA RUGI (P&L) -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    Ikhtisar Laba Rugi (Profit & Loss Statement)
                </h3>
                <span class="text-[10px] font-bold uppercase rounded px-2 py-0.5 bg-indigo-500/20 text-indigo-300">Akrual</span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between items-center text-slate-300">
                    <span>Total Pendapatan (Recognized Revenue)</span>
                    <span class="font-mono font-bold text-emerald-400">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-300">
                    <span>Total Beban Operasional (Expenses)</span>
                    <span class="font-mono font-bold text-rose-400">- Rp {{ number_format($metrics['total_expense'], 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 border-t border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-white">Laba Bersih Perusahaan (Net Profit)</span>
                    <span class="font-mono font-bold text-base {{ $metrics['net_profit'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $metrics['net_profit'] >= 0 ? '+' : '' }} Rp {{ number_format($metrics['net_profit'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400 pt-1">
                    <span>Margin Keuntungan Bersih (Net Margin)</span>
                    <span class="font-mono font-bold text-indigo-300">{{ $metrics['profit_margin'] }}%</span>
                </div>
            </div>
        </div>

        <!-- POSISI KAS & LIKUIDITAS -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                    Posisi Likuiditas & Ketahanan Kas
                </h3>
                <span class="text-[10px] font-bold uppercase rounded px-2 py-0.5 bg-sky-500/20 text-sky-300">Kas Riil</span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between items-center text-slate-300">
                    <span>Total Saldo Kas Aktual</span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($metrics['total_cash'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-300">
                    <span>Rata-Rata Pengeluaran Bulanan (Burn Rate)</span>
                    <span class="font-mono font-bold text-slate-400">Rp {{ number_format($metrics['avg_monthly_burn'], 0, ',', '.') }} / bln</span>
                </div>
                <div class="pt-3 border-t border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-white">Ketahanan Kas (Cash Runway)</span>
                    <span class="font-mono font-bold text-base text-cyan-400">
                        {{ $metrics['cash_runway_months'] }} Bulan
                    </span>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400 pt-1">
                    <span>Status Ketahanan</span>
                    <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        {{ $metrics['cash_runway_status'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- BUDGET VS ACTUAL SUMMARY -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Evaluasi Anggaran (Budget vs Actual)</h3>
            <a href="{{ route('finance.budgets') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Kelola Anggaran ➔</a>
        </div>

        <div class="overflow-x-auto touch-scroll">
            <table class="w-full min-w-[650px] text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Ruang Lingkup / Kategori</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4 text-right">Alokasi Anggaran</th>
                        <th class="py-3 px-4 text-right">Realisasi Pengeluaran</th>
                        <th class="py-3 px-4 text-right">Selisih (Variance)</th>
                        <th class="py-3 px-4 text-center">Utilisasi %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($budgets as $b)
                    @php
                        $util = $b->budgeted_amount > 0 ? round(($b->actual_amount / $b->budgeted_amount) * 100, 1) : 0;
                        $var = (float)$b->budgeted_amount - (float)$b->actual_amount;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-bold text-white uppercase text-[11px] block">{{ $b->scope_type }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $b->project ? $b->project->title : ($b->category ?: 'General Operational') }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400">
                            {{ $b->month ? sprintf('%02d', $b->month) . '/' : '' }}{{ $b->year }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-200">
                            Rp {{ number_format($b->budgeted_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-rose-400">
                            Rp {{ number_format($b->actual_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold {{ $var >= 0 ? 'text-emerald-400' : 'text-rose-500' }}">
                            Rp {{ number_format($var, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[9px] font-mono font-bold rounded-full {{ $util > 100 ? 'bg-rose-500/20 text-rose-400' : 'bg-indigo-500/20 text-indigo-300' }}">
                                {{ $util }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500">
                            Belum ada definisi anggaran aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
