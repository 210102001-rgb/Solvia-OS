@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Piutang Klien & Matriks Umur (Aging Matrix)
                <span class="text-xs bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded-full uppercase">Receivables</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pengendalian piutang tertagih (AR) berdasarkan kelompok jatuh tempo dan mitigasi resiko gagal bayar.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.invoices') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Kelola Invoices
            </a>
            <a href="{{ route('finance.payments') }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Terima Pembayaran
            </a>
        </div>
    </div>

    <!-- AGING BUCKETS SUMMARY -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Current / 0-30 Hari</span>
            <p class="text-lg font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($aging['current'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-emerald-500/80 block mt-1">Belum jatuh tempo</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">1 - 30 Hari Lewat</span>
            <p class="text-lg font-black text-sky-400 font-mono mt-1">Rp {{ number_format($aging['tier_30'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-sky-500/80 block mt-1">Early follow-up</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">31 - 60 Hari Lewat</span>
            <p class="text-lg font-black text-amber-400 font-mono mt-1">Rp {{ number_format($aging['tier_60'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-amber-500/80 block mt-1">Perlu eskalasi</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">61 - 90 Hari Lewat</span>
            <p class="text-lg font-black text-orange-400 font-mono mt-1">Rp {{ number_format($aging['tier_90'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-orange-500/80 block mt-1">Peringatan keras</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-rose-900/40 bg-rose-950/10">
            <span class="text-[10px] uppercase font-bold text-rose-400 block tracking-wider">> 90 Hari Lewat</span>
            <p class="text-lg font-black text-rose-400 font-mono mt-1">Rp {{ number_format($aging['tier_over90'], 0, ',', '.') }}</p>
            <span class="text-[10px] text-rose-500/80 block mt-1">Resiko Macet (Bad Debt)</span>
        </div>
    </div>

    <!-- TOTAL RECEIVABLE BANNER -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold">Total Piutang Belum Tertagih</span>
            <p class="text-2xl font-black text-white font-mono mt-0.5">Rp {{ number_format($aging['total'], 0, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400">Prinsip Akuntansi:</span>
            <p class="text-xs font-semibold text-amber-300">Receivable ≠ Cash (Piutang belum dihitung sebagai likuiditas sebelum masuk ke rekening kas).</p>
        </div>
    </div>

    <!-- AGING INVOICES LIST -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Daftar Tagihan Belum Lunas (Aging Matrix Detail)</h3>
            <span class="text-xs font-mono text-slate-400">{{ count($aging['items']) }} Invoices Tertagih</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">No. Faktur</th>
                        <th class="py-3 px-4">Klien & Project</th>
                        <th class="py-3 px-4">Tgl Terbit</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Kelompok Umur</th>
                        <th class="py-3 px-4 text-right">Sisa Piutang</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($aging['items'] as $item)
                    @php
                        $inv = $item['invoice'];
                        $tierStyle = match($item['tier']) {
                            'current' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            '1-30 days' => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
                            '31-60 days' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                            '61-90 days' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                            default => 'bg-rose-500/20 text-rose-400 border-rose-500/30 font-black animate-pulse'
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-white">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-200 block">{{ $inv->client->company_name ?? 'Klien' }}</span>
                            @if($inv->project)
                                <span class="text-[11px] text-indigo-400 block font-mono">{{ $inv->project->title }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">{{ $inv->issue_date ? $inv->issue_date->format('d M Y') : '-' }}</td>
                        <td class="py-3 px-4 font-mono whitespace-nowrap {{ $item['days_past'] > 0 ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                            {{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}
                            @if($item['days_past'] > 0)
                                <span class="text-[10px] block">({{ $item['days_past'] }} hari lewat)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[9px] uppercase font-bold rounded-full border {{ $tierStyle }}">
                                {{ $item['tier'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                            Rp {{ number_format($item['outstanding'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('finance.payments') }}" class="px-2.5 py-1 rounded bg-emerald-600/30 hover:bg-emerald-600/50 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold transition">
                                Bayar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-500">
                            Semua tagihan lunas. Tidak ada piutang outstanding.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
