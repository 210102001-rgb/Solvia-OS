@extends('layouts.app')

@section('content')
<div x-data="{ expandedLog: null }" class="min-h-screen bg-slate-950 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Audit Trail</h1>
            <p class="text-slate-400 mt-2">Track system activity and data modifications.</p>
        </div>

        <div class="bg-indigo-900/30 border border-indigo-800 rounded-2xl p-4 mb-8 flex items-start gap-4">
            <svg class="w-6 h-6 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-indigo-200/70 text-sm pt-0.5">Audit logs are read-only and cannot be modified. They provide a tamper-evident record of all significant actions within the platform.</p>
        </div>

        <!-- Filters -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-8">
            <form action="{{ route('audit.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <select name="user_id" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                
                <input type="text" name="action" placeholder="Action (e.g. created, updated)" value="{{ $filters['action'] ?? '' }}" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                
                <select name="entity_type" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="">All Entities</option>
                    <option value="User" {{ ($filters['entity_type'] ?? '') == 'User' ? 'selected' : '' }}>User</option>
                    <option value="Team" {{ ($filters['entity_type'] ?? '') == 'Team' ? 'selected' : '' }}>Team</option>
                    <option value="Client" {{ ($filters['entity_type'] ?? '') == 'Client' ? 'selected' : '' }}>Client</option>
                    <option value="Project" {{ ($filters['entity_type'] ?? '') == 'Project' ? 'selected' : '' }}>Project</option>
                </select>
                
                <div class="flex gap-2 items-center">
                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                </div>
                
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-medium px-4 py-2 rounded-xl transition-colors text-sm">
                    Filter Logs
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4 font-medium">Timestamp</th>
                            <th class="px-6 py-4 font-medium">User</th>
                            <th class="px-6 py-4 font-medium">Action</th>
                            <th class="px-6 py-4 font-medium">Entity</th>
                            <th class="px-6 py-4 font-medium">IP Address</th>
                            <th class="px-6 py-4 font-medium">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-bold text-white">
                                        {{ strtoupper(substr($log->actor->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="text-white">{{ $log->actor->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $actionColor = str_contains(strtolower($log->action), 'delete') ? 'bg-rose-900/50 text-rose-400 border-rose-800' : 
                                                  (str_contains(strtolower($log->action), 'create') ? 'bg-emerald-900/50 text-emerald-400 border-emerald-800' : 
                                                  'bg-blue-900/50 text-blue-400 border-blue-800');
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-mono border {{ $actionColor }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ $log->entity_type }}</span>
                                <span class="text-slate-500 text-xs ml-1">#{{ $log->entity_id }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="px-6 py-4">
                                @php $hasDetails = !empty($log->new_values) || !empty($log->description); @endphp
                                @if($hasDetails)
                                    <button @click="expandedLog = expandedLog === {{ $log->id }} ? null : {{ $log->id }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium flex items-center gap-1">
                                        View
                                        <svg class="w-3 h-3 transition-transform" :class="expandedLog === {{ $log->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                @else
                                    <span class="text-slate-600 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($log->new_values) || !empty($log->description))
                        <tr x-show="expandedLog === {{ $log->id }}" style="display: none;" class="bg-slate-950/50">
                            <td colspan="6" class="px-6 py-4">
                                <div class="space-y-2 text-xs">
                                    @if($log->description)
                                    <p class="text-slate-300"><span class="text-slate-500 font-bold">Description:</span> {{ $log->description }}</p>
                                    @endif
                                    @if(!empty($log->new_values))
                                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 font-mono text-slate-300 overflow-x-auto">
                                        <pre class="text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">No audit logs found matching your criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-900">
                {{ $logs->links() ?? '' }}
            </div>
        </div>
    </div>
</div>
@endsection
