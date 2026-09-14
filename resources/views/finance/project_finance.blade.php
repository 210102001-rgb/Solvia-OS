@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Project Financial Snapshot & Labor Cost
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase">Profit Engine</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Analisis profitabilitas tiap project menggabungkan biaya operasional langsung & biaya tenaga kerja (Labor Cost).</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300">
                Formula: Labor Rate = Gaji Pokok / 160 Jam
            </span>
        </div>
    </div>

    <!-- CORE PRINCIPLE CALLOUT -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/30 to-slate-900 border border-slate-800">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl bg-indigo-500/20 text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white">Prinsip Akuntansi: Project Revenue ≠ Project Profit</h4>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Kontrak bernilai besar tidak menjamin profit jika alokasi jam kerja karyawan (Daily Progress) membengkak. Sistem secara cerdas mengalkulasi <span class="text-indigo-300 font-semibold">Labor Cost analitis</span> ke dalam beban proyek tanpa menduplikasi pencatatan kas payroll perusahaan.
                </p>
            </div>
        </div>
    </div>

    <!-- PROJECTS SNAPSHOT TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Matriks Profitabilitas Proyek (Real-Time)</h3>
            <span class="text-xs font-mono text-slate-400">{{ count($projectSnapshots) }} Proyek Dianalisis</span>
        </div>

        <div class="overflow-x-auto touch-scroll">
            <table class="w-full min-w-[760px] text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Project & Klien</th>
                        <th class="py-3 px-4 text-right">Nilai Kontrak</th>
                        <th class="py-3 px-4 text-right">Direct Expense</th>
                        <th class="py-3 px-4 text-right">Labor Cost (Jam)</th>
                        <th class="py-3 px-4 text-right">Total Biaya Aktual</th>
                        <th class="py-3 px-4 text-right">Net Profit</th>
                        <th class="py-3 px-4 text-center">Margin %</th>
                        <th class="py-3 px-4 text-center">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($projectSnapshots as $snap)
                    @php
                        $proj = $snap['project'];
                        $ratingStyle = match($snap['rating']) {
                            'HIGH PROFIT' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            'HEALTHY' => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
                            'LOW MARGIN' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                            'LOSS' => 'bg-rose-500/20 text-rose-400 border-rose-500/30 font-black animate-pulse',
                            default => 'bg-slate-700 text-slate-300'
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-bold text-white block">{{ $proj->title }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $proj->client->company_name ?? 'Internal / Retail' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-200 whitespace-nowrap">
                            Rp {{ number_format($snap['contract_value'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-rose-300 whitespace-nowrap">
                            Rp {{ number_format($snap['direct_cost'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <span class="font-mono text-indigo-300 font-bold block">Rp {{ number_format($snap['labor_cost'], 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-500 block">({{ number_format($snap['labor_hours'], 1) }} Jam)</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-rose-400 whitespace-nowrap">
                            Rp {{ number_format($snap['total_actual_cost'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $snap['project_profit'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $snap['project_profit'] >= 0 ? '+' : '' }} Rp {{ number_format($snap['project_profit'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold whitespace-nowrap {{ $snap['profit_margin'] >= 20 ? 'text-emerald-400' : ($snap['profit_margin'] >= 0 ? 'text-amber-400' : 'text-rose-400') }}">
                            {{ $snap['profit_margin'] }}%
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[9px] uppercase font-bold rounded-full border {{ $ratingStyle }}">
                                {{ $snap['rating'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-500">
                            Belum ada proyek yang dapat dianalisis keuangannya.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
