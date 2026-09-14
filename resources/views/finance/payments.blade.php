@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addPaymentModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Penerimaan Pembayaran (Payments In)
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full uppercase">Realized Cash In</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pencatatan realisasi pelunasan tagihan invoice klien. Mengurangi piutang & menambah saldo kas.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="addPaymentModal = true" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Catat Penerimaan Kas
            </button>
        </div>
    </div>

    <!-- PAYMENTS TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Riwayat Pelunasan Invoice Klien</h3>
            <span class="text-xs font-mono text-slate-400">{{ $payments->total() }} Bukti Penerimaan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">No. Pembayaran</th>
                        <th class="py-3 px-4">Tanggal Terima</th>
                        <th class="py-3 px-4">Invoice & Klien</th>
                        <th class="py-3 px-4">Rekening Tujuan</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4">Ref / Bukti</th>
                        <th class="py-3 px-4 text-right">Jumlah Masuk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($payments as $pay)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-200">{{ $pay->payment_number }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">{{ $pay->payment_date ? $pay->payment_date->format('d M Y') : '-' }}</td>
                        <td class="py-3 px-4">
                            @if($pay->invoice)
                                <a href="{{ route('finance.invoices') }}" class="font-bold text-indigo-400 hover:underline block font-mono">
                                    {{ $pay->invoice->invoice_number }}
                                </a>
                                <span class="text-[11px] text-slate-400 block">{{ $pay->invoice->client->company_name ?? 'Klien' }}</span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap font-semibold text-slate-300">
                            {{ $pay->account->account_name ?? '—' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded bg-slate-800 text-slate-300 border border-slate-700">
                                {{ str_replace('_', ' ', $pay->payment_method) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">
                            {{ $pay->reference_number ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                            + Rp {{ number_format($pay->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-500">
                            Belum ada riwayat pembayaran invoice yang masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL RECORD PAYMENT -->
    <div x-show="addPaymentModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
        <div @click.away="addPaymentModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative text-left">
            <h3 class="text-lg font-black text-white mb-1">Catat Penerimaan Pembayaran</h3>
            <p class="text-xs text-slate-400 mb-5">Penerimaan dana dari tagihan invoice klien. Transaksi ini akan otomatis menambah kas dan mengurangi piutang.</p>

            <form action="{{ route('finance.payments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Invoice yang Dibayar *</label>
                    <select name="invoice_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Pilih Invoice Tertagih --</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}">
                                {{ $inv->invoice_number }} - {{ $inv->client->company_name ?? 'Client' }} (Sisa: Rp {{ number_format($inv->outstanding_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Rekening Tujuan Masuk *</label>
                        <select name="financial_account_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-emerald-500">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->provider ?? $acc->bank_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah Masuk (Rp) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="0" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 font-mono focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Terima *</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-emerald-500">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="e_wallet">E-Wallet</option>
                            <option value="payment_gateway">Payment Gateway</option>
                            <option value="cash">Tunai / Cash</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">No. Referensi / No. Transaksi Bank</label>
                    <input type="text" name="reference_number" placeholder="Contoh: TRF-BCA-9821893" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 font-mono focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Pembayaran</label>
                    <textarea name="notes" rows="2" placeholder="Keterangan tambahan..." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="addPaymentModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-emerald-600/20">
                        Simpan Penerimaan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
