@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ assetModal: false, assignModal: false, maintenanceModal: false, selectedAsset: null }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                {{ Auth::user()->isSuperAdmin() ? 'Asset Registry' : 'My Assigned Assets' }}
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $assets->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                {{ Auth::user()->isSuperAdmin() ? 'Physical asset lifecycle — from purchase to assignment to retirement' : 'Physical equipment & hardware allocated to you' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="assetModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ Auth::user()->isSuperAdmin() ? 'Register Asset' : '+ Add My Asset' }}
            </button>
        </div>
    </div>

    @if(Auth::user()->isSuperAdmin())
    <!-- TOTAL VALUE STRIP (Super Admin Only) -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
        <div>
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Asset Portfolio Value</p>
            <p class="text-2xl font-black text-white font-mono mt-0.5">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</p>
        </div>
        <svg class="w-12 h-12 text-indigo-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9a2 2 0 00-2-2h-4M9 21H5a2 2 0 01-2-2V9a2 2 0 012-2h4"/>
        </svg>
    </div>
    @endif

    <!-- ASSET CARDS — Mobile-Adaptive (Section BR) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($assets as $asset)
        @php
            $lifecycleColor = match($asset->lifecycle_status) {
                'in_use','assigned' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                'available' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                'retired','disposed' => 'bg-slate-800 text-slate-400 border-slate-700',
                default => 'bg-slate-800 text-slate-300 border-slate-700',
            };
            $conditionColor = match($asset->condition) {
                'excellent' => 'text-emerald-400',
                'good' => 'text-indigo-400',
                'fair' => 'text-amber-400',
                'damaged' => 'text-rose-400',
                default => 'text-slate-400',
            };
            $warrantyDays = $asset->warranty ? now()->diffInDays($asset->warranty->expiry_date, false) : null;
        @endphp
        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500/30 transition flex flex-col justify-between group shadow-md">
            <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="text-[9px] font-mono font-bold text-indigo-400">{{ $asset->asset_tag }}</span>
                        <h3 class="font-bold text-white text-sm mt-0.5">{{ $asset->brand }} {{ $asset->model }}</h3>
                        @if($asset->serial_number)
                            <p class="text-[10px] text-slate-500 font-mono">S/N: {{ $asset->serial_number }}</p>
                        @endif
                    </div>
                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border flex-shrink-0 {{ $lifecycleColor }}">
                        {{ str_replace('_', ' ', $asset->lifecycle_status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs mt-3">
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Assigned To</p>
                        <p class="text-slate-200 font-semibold mt-0.5">{{ $asset->currentHolder ? $asset->currentHolder->name : 'Available' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Condition</p>
                        <p class="font-semibold mt-0.5 {{ $conditionColor }} capitalize">{{ $asset->condition }}</p>
                    </div>
                    @if(Auth::user()->isSuperAdmin())
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Value</p>
                        <p class="text-slate-200 font-mono mt-0.5">Rp {{ number_format($asset->accumulated_cost, 0, ',', '.') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Warranty</p>
                        @if($asset->warranty)
                            <p class="font-mono mt-0.5 {{ $warrantyDays !== null && $warrantyDays < 30 ? 'text-amber-400' : 'text-slate-400' }}">
                                {{ $asset->warranty->expiry_date->format('d M Y') }}
                                @if($warrantyDays !== null && $warrantyDays >= 0 && $warrantyDays < 30)
                                    <span class="text-[9px] text-amber-400 font-bold block">{{ $warrantyDays }}d left</span>
                                @elseif($warrantyDays !== null && $warrantyDays < 0)
                                    <span class="text-[9px] text-rose-400 font-bold block">Expired</span>
                                @endif
                            </p>
                        @else
                            <p class="text-slate-500 mt-0.5">No warranty</p>
                        @endif
                    </div>
                </div>

                @if($asset->specs)
                    <p class="text-[10px] text-slate-500 mt-3 truncate">{{ $asset->specs }}</p>
                @endif
            </div>

            <!-- ACTIONS (Super Admin) -->
            @if(Auth::user()->isSuperAdmin())
            <div class="mt-4 pt-3 border-t border-slate-800 flex items-center gap-2 text-xs">
                @if($asset->lifecycle_status === 'available')
                    <button
                        @click="selectedAsset = {{ $asset->id }}; assignModal = true"
                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex-1 text-center">
                        Assign
                    </button>
                @elseif(in_array($asset->lifecycle_status, ['in_use', 'assigned']))
                    <form action="{{ route('resources.assets.return', $asset->id) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="condition" value="{{ $asset->condition }}">
                        <input type="hidden" name="notes" value="Returned via asset management">
                        <button type="submit" onclick="return confirm('Mark asset {{ $asset->asset_tag }} as returned?')"
                            class="w-full px-3 py-1.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs transition">
                            Return
                        </button>
                    </form>
                @endif
                <button
                    @click="selectedAsset = {{ $asset->id }}; maintenanceModal = true"
                    class="px-3 py-1.5 rounded-xl bg-amber-700 hover:bg-amber-600 text-white font-bold text-xs transition">
                    Maintenance
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No assets registered yet. Register your first asset using the button above.
        </div>
        @endforelse
    </div>

    <div>{{ $assets->links() }}</div>

    <!-- MODAL: REGISTER ASSET -->
    <div x-cloak x-show="assetModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="assetModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="assetModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="assetModal = false">
            <div x-show="assetModal" x-transition @click.stop class="relative z-20 w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <form action="{{ route('resources.assets.store') }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900">
                        <h3 class="font-bold text-base text-white">{{ Auth::user()->isSuperAdmin() ? 'Register New Asset' : 'Add My Equipment / Asset' }}</h3>
                        <button type="button" @click="assetModal = false" class="text-slate-400 hover:text-white text-xl font-bold leading-none">&times;</button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Asset Tag *</label>
                                <input type="text" name="asset_tag" required value="AST-{{ strtoupper(Str::random(6)) }}" placeholder="e.g. LAPTOP-001" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Condition *</label>
                                <select name="condition" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                                    <option value="excellent">Excellent</option>
                                    <option value="good" selected>Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Brand *</label>
                                <input type="text" name="brand" required placeholder="e.g. Apple, Dell, Lenovo, Asus" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Model *</label>
                                <input type="text" name="model" required placeholder="e.g. MacBook Pro M3, ThinkPad" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid {{ Auth::user()->isSuperAdmin() ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Serial Number (Optional)</label>
                                <input type="text" name="serial_number" placeholder="Manufacturer S/N" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            @if(Auth::user()->isSuperAdmin())
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Purchase Cost (Rp) *</label>
                                <input type="number" name="purchase_cost" min="0" required placeholder="e.g. 25000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            @else
                            <input type="hidden" name="purchase_cost" value="0">
                            <input type="hidden" name="current_holder_id" value="{{ Auth::id() }}">
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Specs / Description</label>
                            <input type="text" name="specs" placeholder="e.g. 16GB RAM, 512GB SSD, M3 Pro Chip" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>

                        @if(Auth::user()->isSuperAdmin())
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Assign To (Optional)</label>
                            <select name="current_holder_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="">Available — not yet assigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ str_replace('_', ' ', $user->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Warranty Provider</label>
                                <input type="text" name="warranty_provider" placeholder="e.g. Apple Care, Dell Support" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-950/80 border-t border-slate-800 flex justify-end gap-2 shrink-0">
                        <button type="button" @click="assetModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition">
                            {{ Auth::user()->isSuperAdmin() ? 'Register Asset' : 'Save My Asset' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: ASSIGN ASSET -->
    <div x-cloak x-show="assignModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="assignModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="assignModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="assignModal = false">
            <div x-show="assignModal" x-transition @click.stop class="relative z-20 w-full max-w-sm rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-sm text-white">Assign Asset to User</h3>
                    <button @click="assignModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form :action="'/resources/assets/' + selectedAsset + '/assign'" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Assign To *</label>
                        <select name="user_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Condition at Assignment *</label>
                        <select name="condition" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="excellent">Excellent</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: LOG MAINTENANCE -->
    <div x-cloak x-show="maintenanceModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="maintenanceModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="maintenanceModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="maintenanceModal = false">
            <div x-show="maintenanceModal" x-transition @click.stop class="relative z-20 w-full max-w-sm rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-sm text-white">Log Maintenance</h3>
                    <button @click="maintenanceModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form :action="'/resources/assets/' + selectedAsset + '/maintenance'" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Type *</label>
                            <select name="maintenance_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                                <option value="preventative">Preventative</option>
                                <option value="repair">Repair</option>
                                <option value="upgrade">Upgrade</option>
                                <option value="inspection">Inspection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Status *</label>
                            <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Scheduled Date *</label>
                            <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Cost (Rp) *</label>
                            <input type="number" name="cost" min="0" value="0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Technician / Vendor</label>
                        <input type="text" name="technician_vendor" placeholder="Name or company" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="maintenanceModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs">Log Maintenance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
