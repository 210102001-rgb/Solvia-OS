@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    addModal: false,
    payModal: false,
    activePayable: null,
    payActionUrl: ''
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Hutang Usaha (Accounts Payable)
                <span class="text-xs bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded-full uppercase">Vendor Liabilities</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Kewajiban pembayaran kepada vendor, freelancer, supplier & tagihan pihak ketiga.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="addModal = true" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-rose-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Catat Hutang Baru
            </button>
        </div>
    </div>

    <!-- METRICS BANNER -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-rose-950/20 to-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Hutang Berjalan</span>
            <p class="text-2xl sm:text-3xl font-black text-white font-mono mt-0.5">Rp {{ number_format($totalPayable, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Prinsip Akuntansi: <span class="text-slate-300 font-semibold">Payable ≠ Expense yang sudah dibayar</span>. Kas hanya berkurang saat pelunasan dilakukan.</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-amber-950/20 to-slate-900 border border-slate-800">
            <span class="text-xs text-amber-400 uppercase font-bold tracking-wider">Hutang Melewati Jatuh Tempo</span>
            <p class="text-2xl sm:text-3xl font-black text-amber-400 font-mono mt-0.5">Rp {{ number_format($overduePayable, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Kewajiban mendesak yang memerlukan penyelesaian segera guna menjaga reputasi perusahaan.</p>
        </div>
    </div>

    <!-- PAYABLES TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Daftar Kewajiban Pembayaran (Payables)</h3>
            <span class="text-xs font-mono text-slate-400">{{ $payables->total() }} Records</span>
        </div>

        <div class="overflow-x-auto touch-scroll">
            <table class="w-full min-w-[700px] text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Kode & Judul</th>
                        <th class="py-3 px-4">Pihak Kreditur / Vendor</th>
                        <th class="py-3 px-4">Project</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Total Kewajiban</th>
                        <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($payables as $pay)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-slate-200 block">{{ $pay->payable_code }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $pay->title }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-white block">{{ $pay->vendor_name }}</span>
                            <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded bg-slate-800 text-slate-400 border border-slate-700 inline-block mt-0.5">
                                {{ $pay->creditor_type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($pay->project)
                                <span class="px-2 py-0.5 text-[10px] rounded bg-slate-800 text-indigo-300 border border-slate-700 font-mono">
                                    {{ Str::limit($pay->project->title, 16) }}
                                </span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono whitespace-nowrap {{ $pay->is_overdue ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                            {{ $pay->due_date ? $pay->due_date->format('d M Y') : '-' }}
                            @if($pay->is_overdue)
                                <span class="text-[10px] text-rose-500 block font-sans">Jatuh Tempo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-300 whitespace-nowrap">
                            Rp {{ number_format($pay->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-400 whitespace-nowrap">
                            Rp {{ number_format($pay->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                            Rp {{ number_format($pay->outstanding_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @php
                                $statusStyle = match($pay->status) {
                                    'paid' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                    'partial' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                    default => 'bg-slate-700 text-slate-300 border-slate-600'
                                };
                            @endphp
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full border {{ $statusStyle }}">
                                {{ $pay->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($pay->status !== 'paid')
                                <button @click="
                                    activePayable = {{ json_encode($pay) }};
                                    payActionUrl = '{{ route('finance.payables.pay', $pay->id) }}';
                                    payModal = true;
                                " class="px-2.5 py-1 rounded bg-rose-600/30 hover:bg-rose-600/50 text-rose-300 border border-rose-500/30 text-[10px] font-bold transition">
                                    Bayar
                                </button>
                            @else
                                <span class="text-[11px] text-emerald-400 font-mono">Lunas ✓</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-slate-500">
                            Tidak ada data kewajiban hutang yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payables->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payables->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL CREATE PAYABLE -->
    <div x-show="addModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
        <div @click.away="addModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative text-left">
            <h3 class="text-lg font-black text-white mb-1">Catat Hutang Usaha (Payable)</h3>
            <p class="text-xs text-slate-400 mb-5">Daftarkan invoice/tagihan dari vendor atau freelancer sebelum dilakukan pembayaran kas.</p>

            <form action="{{ route('finance.payables.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Judul / Perihal Tagihan *</label>
                    <input type="text" name="title" required placeholder="Contoh: Tagihan Server Dedicated Q3" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Vendor / Kreditur *</label>
                        <input type="text" name="vendor_name" required placeholder="Contoh: PT Cloud Hostindo" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe Kreditur *</label>
                        <select name="creditor_type" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                            <option value="vendor">Vendor</option>
                            <option value="freelancer">Freelancer</option>
                            <option value="supplier">Supplier</option>
                            <option value="service_provider">Service Provider</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori Beban *</label>
                        <input type="text" name="category" required placeholder="Infrastructure, Software, etc." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Project Terkait (Opsional)</label>
                        <select name="project_id" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                            <option value="">-- Beban Umum Perusahaan --</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="0" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 font-mono focus:ring-1 focus:ring-rose-500">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tgl Terbit *</label>
                        <input type="date" name="issue_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jatuh Tempo *</label>
                        <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan / No. Kontrak</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-rose-600/20">
                        Simpan Hutang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PAY PAYABLE -->
    <div x-show="payModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
        <div @click.away="payModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl relative text-left">
            <h3 class="text-lg font-black text-white mb-1">Pelunasan Hutang Usaha</h3>
            <p class="text-xs text-slate-400 mb-4">Pengeluaran kas akan dicatat di buku besar dan mengurangi saldo kas terpilih.</p>

            <template x-if="activePayable">
                <div class="p-3 mb-4 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                    <p class="font-bold text-white" x-text="activePayable.title"></p>
                    <p class="text-slate-400">Vendor: <span class="text-slate-200" x-text="activePayable.vendor_name"></span></p>
                    <p class="text-slate-400 mt-1">Sisa Hutang: <span class="text-rose-400 font-mono font-bold" x-text="'Rp ' + Number(activePayable.amount - activePayable.paid_amount).toLocaleString('id-ID')"></span></p>
                </div>
            </template>

            <form :action="payActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Rekening Sumber Pembayaran *</label>
                    <select name="financial_account_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal Bayar (Rp) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="0" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 font-mono focus:ring-1 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Bayar *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan / Bukti Transfer</label>
                    <textarea name="notes" rows="2" placeholder="Nomor referensi atau catatan pelunasan..." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="payModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-rose-600/20">
                        Konfirmasi Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
