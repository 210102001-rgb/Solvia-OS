@extends('layouts.app')

@section('content')
<div x-data="{ showCreateModal: {{ $errors->any() ? 'true' : 'false' }}, showManageModal: false, manageTeam: null }" class="min-h-screen bg-slate-950 p-4 md:p-8">
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
                <h1 class="text-3xl font-bold text-white">Teams</h1>
                <p class="text-slate-400 mt-2">Organize your organization into teams and departments.</p>
            </div>
            <button @click="showCreateModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl transition-colors">
                + Create Team
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($teams as $team)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ $team->name }}</h3>
                        <p class="text-slate-400 text-sm mt-1">{{ $team->description }}</p>
                    </div>
                </div>
                
                <div class="mb-6 flex-1">
                    <div class="mb-4">
                        <span class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Team Lead</span>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-indigo-900 flex items-center justify-center text-indigo-300 text-xs font-bold">
                                {{ substr($team->lead->name ?? '?', 0, 2) }}
                            </div>
                            <span class="text-sm text-white">{{ $team->lead->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="block text-xs font-medium text-slate-400 uppercase tracking-wider">Members ({{ $team->members_count ?? $team->members->count() }})</span>
                        </div>
                        <div class="flex -space-x-2">
                            @foreach($team->members->take(5) as $member)
                            <div class="w-8 h-8 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-white text-xs font-bold" title="{{ $member->name }}">
                                {{ substr($member->name, 0, 2) }}
                            </div>
                            @endforeach
                            @if($team->members->count() > 5)
                            <div class="w-8 h-8 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-slate-400 text-xs font-medium">
                                +{{ $team->members->count() - 5 }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-800">
                    <span class="text-xs text-slate-500">Created {{ $team->created_at ? $team->created_at->format('M Y') : '' }}</span>
                    <div class="flex gap-2">
                        <button @click="showManageModal = true; manageTeam = {{ $team->toJson() }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">
                            Manage
                        </button>
                        <form action="{{ route('company.teams.destroy', $team->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this team?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium ml-2">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-slate-400">
                No teams found. Create your first team!
            </div>
            @endforelse
        </div>
    </div>

    <!-- Create Team Modal -->
    <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showCreateModal = false"></div>
        
        <!-- Modal Dialog Box -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="showCreateModal = false">
            <div x-show="showCreateModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <form action="{{ route('company.teams.store') }}" method="POST" class="flex flex-col overflow-hidden">
                    @csrf
                    <div class="p-6 overflow-y-auto space-y-4 flex-1">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 class="text-xl font-bold text-white">Create New Team</h3>
                            <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-white text-2xl font-bold leading-none">&times;</button>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Team Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Engineering, Creative Studio" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Team Code (Optional)</label>
                            <input type="text" name="code" placeholder="e.g. ENG, DES (auto-generated if empty)" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Description</label>
                            <textarea name="description" rows="3" placeholder="Briefly describe this team's scope..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Team Lead (Optional)</label>
                            <select name="lead_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                <option value="">-- No Team Lead Assigned --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ str_replace('_', ' ', $user->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-colors">Create Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manage Members Modal -->
    <div x-cloak x-show="showManageModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showManageModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showManageModal = false"></div>
        
        <!-- Modal Dialog Box -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="showManageModal = false">
            <div x-show="showManageModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-8 max-h-[90vh] flex flex-col">
                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-xl font-bold text-white" x-text="'Manage ' + (manageTeam?.name || 'Team') + ' Members'"></h3>
                        <button type="button" @click="showManageModal = false" class="text-slate-400 hover:text-white text-2xl font-bold leading-none">&times;</button>
                    </div>
                    
                    <!-- Add Member Form -->
                    <form :action="`/company/teams/${manageTeam?.id}/members`" method="POST" class="flex gap-2">
                        @csrf
                        <select name="user_id" required class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select active user to add...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ str_replace('_', ' ', $user->role) }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-colors shadow-md">Add</button>
                    </form>

                    <!-- Active Team Members list -->
                    <div class="mt-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Current Team Members</h4>
                        <div class="space-y-2">
                            <template x-for="member in (manageTeam?.members || [])" :key="member.id">
                                <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-indigo-900/50 text-indigo-300 font-bold flex items-center justify-center text-[10px]" x-text="member.name.substring(0,2).toUpperCase()"></div>
                                        <div>
                                            <span class="font-medium text-white block" x-text="member.name"></span>
                                            <span class="text-[11px] text-slate-500" x-text="member.email"></span>
                                        </div>
                                    </div>
                                    <form :action="`/company/teams/${manageTeam?.id}/members/${member.id}`" method="POST" onsubmit="return confirm('Remove this user from the team?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-xs">Remove</button>
                                    </form>
                                </div>
                            </template>
                            <template x-if="!manageTeam?.members || manageTeam.members.length === 0">
                                <div class="text-xs text-slate-500 text-center py-4 bg-slate-950 rounded-xl border border-slate-800">
                                    No members assigned to this team yet.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-950/60 border-t border-slate-800 flex justify-end">
                    <button type="button" @click="showManageModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
