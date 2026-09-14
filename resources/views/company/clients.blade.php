@extends('layouts.app')

@section('content')
<div x-data="{ showClientModal: {{ $errors->any() ? 'true' : 'false' }}, editClient: null }" class="min-h-screen bg-slate-950 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-white text-lg">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-white text-lg">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
                <div class="font-semibold mb-1">Please correct the following errors:</div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Clients</h1>
                <p class="text-slate-400 mt-2">Manage your client relationships and contact information.</p>
            </div>
            <button @click="showClientModal = true; editClient = null" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl transition-colors">
                + Add Client
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">Total Clients</h3>
                <p class="text-3xl font-bold text-white mt-2">{{ $clients->total() ?? 0 }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">Active</h3>
                <p class="text-3xl font-bold text-emerald-500 mt-2">{{ $activeCount ?? 0 }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">Total Projects</h3>
                <p class="text-3xl font-bold text-indigo-400 mt-2">{{ $projectsCount ?? 0 }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-8">
            <form action="{{ route('company.clients') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="search" placeholder="Search clients..." value="{{ $filters['search'] ?? '' }}" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                <select name="status" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-medium px-4 py-2 rounded-xl transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4 font-medium">Client</th>
                            <th class="px-6 py-4 font-medium">Contact Person</th>
                            <th class="px-6 py-4 font-medium">Contact Info</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium">Projects</th>
                            <th class="px-6 py-4 font-medium">Since</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($clients as $client)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white">{{ $client->name }}</div>
                                <div class="text-xs text-slate-500">{{ $client->client_code }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $client->contact_person ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-xs">{{ $client->email ?? '-' }}</div>
                                <div class="text-xs">{{ $client->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-900/50 text-emerald-400',
                                        'inactive' => 'bg-slate-800 text-slate-400',
                                    ];
                                    $statusColor = $statusColors[$client->status] ?? 'bg-slate-800 text-slate-400';
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $client->projects_count ?? 0 }}</td>
                            <td class="px-6 py-4">{{ $client->created_at ? $client->created_at->format('M Y') : '-' }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" @click="editClient = {{ json_encode($client) }}; showClientModal = true" class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs transition-colors">Edit</button>
                                <form action="{{ route('company.clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-xs transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">No clients found. Click "+ Add Client" to create your first client.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clients->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-900">
                {{ $clients->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Client Modal -->
    <div x-cloak x-show="showClientModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showClientModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showClientModal = false"></div>
        
        <!-- Modal Dialog Box -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="showClientModal = false">
            <div x-show="showClientModal" x-transition @click.stop class="relative z-20 w-full max-w-2xl rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <form :action="editClient ? (`/company/clients/${editClient.id}`) : '{{ route('company.clients.store') }}'" method="POST" class="flex flex-col overflow-hidden">
                    @csrf
                    <template x-if="editClient">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between bg-slate-900/50">
                        <h3 class="text-xl font-bold text-white" x-text="editClient ? 'Edit Client' : 'Add New Client'"></h3>
                        <button type="button" @click="showClientModal = false" class="text-slate-400 hover:text-white text-2xl font-bold leading-none">&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto space-y-6 flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Client Info -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">Client Info</h4>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Client Code *</label>
                                    <input type="text" name="client_code" :value="editClient ? editClient.client_code : 'CLT-{{ date('Y') }}-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}'" required placeholder="e.g. CLT-001" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Company / Client Name *</label>
                                    <input type="text" name="name" :value="editClient?.name" required placeholder="e.g. PT Mandiri Solusi" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status *</label>
                                    <select name="status" :value="editClient?.status || 'active'" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Website</label>
                                    <input type="text" name="website" :value="editClient?.website" placeholder="https://clientwebsite.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">Contact Information</h4>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Contact Person</label>
                                    <input type="text" name="contact_person" :value="editClient?.contact_person" placeholder="e.g. Bpk. Hendra Gunawan" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                                    <input type="email" name="email" :value="editClient?.email" placeholder="e.g. hendra@client.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone Number</label>
                                    <input type="text" name="phone" :value="editClient?.phone" placeholder="e.g. +62 812-3456-7890" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Office Address</label>
                                    <input type="text" name="address" :value="editClient?.address" placeholder="e.g. Sudirman, Jakarta" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Internal Notes</label>
                            <textarea name="notes" rows="2" placeholder="Special requirements, billing preferences, etc." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm" x-text="editClient?.notes"></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex justify-end gap-3">
                        <button type="button" @click="showClientModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-colors" x-text="editClient ? 'Update Client' : 'Save Client'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

