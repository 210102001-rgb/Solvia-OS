@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Keuangan Aset & Kapitalisasi (Asset Finance)
                <span class="text-xs bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded-full uppercase">Capital Expenditure</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Monitoring kapitalisasi aset perangkat keras, biaya maintenance, dan nilai investasi peralatan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('resources.assets') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Kelola Inventaris Fisik
            </a>
        </div>
    </div>

    <!-- CAPITALIZATION PRINCIPLE -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-amber-950/20 to-slate-900 border border-slate-800">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl bg-amber-500/20 text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white">Prinsip Akuntansi: Asset ≠ Expense Langsung</h4>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Pembelian laptop, server, atau workstation merupakan belanja modal (Capex) yang menambah nilai kekayaan perusahaan pada neraca. Biaya yang diakui sebagai beban operasional berjalan hanyalah <span class="text-amber-300 font-semibold">biaya perbaikan (maintenance)</span> atau depresiasi berkala.
                </p>
            </div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Nilai Perolehan (Capex)</span>
            <p class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($totalPurchaseCost, 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-500 block mt-1">{{ $assets->count() }} Unit Terdaftar</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-rose-400 block tracking-wider">Total Biaya Perawatan (Opex)</span>
            <p class="text-2xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($totalMaintenanceCost, 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-500 block mt-1">Service, sparepart & upkeep</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-amber-400 block tracking-wider">Total Akumulasi Biaya Aset</span>
            <p class="text-2xl font-black text-amber-400 font-mono mt-1">Rp {{ number_format($totalAccumulatedCost, 0, ',', '.') }}</p>
            <span class="text-[10px] text-slate-500 block mt-1">Capex + Maintenance Total</span>
        </div>
    </div>

    <!-- ASSET VALUATION TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Buku Nilai Finansial Aset Fisik</h3>
            <span class="text-xs font-mono text-slate-400">{{ $assets->count() }} Assets</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Kode & Nama Aset</th>
                        <th class="py-3 px-4">Pengguna / Resource</th>
                        <th class="py-3 px-4">Tgl Pembelian</th>
                        <th class="py-3 px-4 text-right">Harga Beli Awal</th>
                        <th class="py-3 px-4 text-right">Biaya Perawatan</th>
                        <th class="py-3 px-4 text-right">Akumulasi Biaya</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($assets as $ast)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[10px] text-amber-400 font-bold block">{{ $ast->asset_code }}</span>
                            <span class="font-bold text-white block">{{ $ast->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            {{ $ast->resource ? $ast->resource->name : 'Shared Pool' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">
                            {{ $ast->purchase_date ? \Carbon\Carbon::parse($ast->purchase_date)->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-200 whitespace-nowrap">
                            Rp {{ number_format($ast->purchase_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-rose-400 whitespace-nowrap">
                            Rp {{ number_format($ast->maintenance_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-amber-400 whitespace-nowrap">
                            Rp {{ number_format($ast->accumulated_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">
                                {{ $ast->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-500">
                            Belum ada aset fisik yang terdata dalam sistem keuangan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
