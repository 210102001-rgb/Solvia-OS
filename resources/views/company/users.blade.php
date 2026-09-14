@extends('layouts.app')

@section('content')
<div x-data="{ showAddModal: {{ $errors->any() ? 'true' : 'false' }}, showEditModal: false, editUser: {} }" class="space-y-6">
    <div class="space-y-6">
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
                <div class="font-semibold mb-1">Please correct the errors below:</div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Team Members</h1>
                <p class="text-slate-400 mt-2">Manage your team members and their roles.</p>
            </div>
            <button @click="showAddModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl transition-colors">
                + Add User
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">Total Users</h3>
                <p class="text-3xl font-bold text-white mt-2">{{ $users->total() ?? 0 }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">Active</h3>
                <p class="text-3xl font-bold text-emerald-500 mt-2">{{ $activeCount ?? 0 }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="text-slate-400 text-sm font-medium">By Role</h3>
                <div class="mt-2 text-white text-sm">
                    <div>Admins: {{ $adminCount ?? 0 }}</div>
                    <div>Developers: {{ $devCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-8">
            <form action="{{ route('company.users') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="search" placeholder="Search users..." value="{{ $filters['search'] ?? '' }}" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                <select name="role" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ ($filters['role'] ?? '') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="content_creator" {{ ($filters['role'] ?? '') == 'content_creator' ? 'selected' : '' }}>Content Creator</option>
                    <option value="frontend_developer" {{ ($filters['role'] ?? '') == 'frontend_developer' ? 'selected' : '' }}>Frontend Developer</option>
                </select>
                <select name="status" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-medium px-4 py-2 rounded-xl transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($users as $user)
            @php
                $roleColors = [
                    'super_admin' => 'bg-purple-900 text-purple-300',
                    'content_creator' => 'bg-blue-900 text-blue-300',
                    'designer' => 'bg-pink-900 text-pink-300',
                    'frontend_developer' => 'bg-indigo-900 text-indigo-300',
                    'backend_developer' => 'bg-green-900 text-green-300',
                    'iot_engineer' => 'bg-cyan-900 text-cyan-300',
                    'viewer' => 'bg-gray-800 text-gray-300',
                ];
                $roleColor = $roleColors[$user->role] ?? 'bg-slate-800 text-slate-300';
            @endphp
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div>
                            <h3 class="text-white font-medium">{{ $user->name }}</h3>
                            <p class="text-slate-400 text-sm">{{ $user->department ?? 'No Department' }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-medium {{ $roleColor }}">
                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
                
                <div class="space-y-2 mb-6 flex-1">
                    <div class="flex items-center text-sm">
                        <span class="text-slate-400 w-20">Email:</span>
                        <span class="text-white">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-slate-400 w-20">Phone:</span>
                        <span class="text-white">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-slate-400 w-20">Joined:</span>
                        <span class="text-white">{{ $user->join_date ? \Carbon\Carbon::parse($user->join_date)->format('M d, Y') : '-' }}</span>
                    </div>
                    <div class="flex items-center text-sm mt-2">
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $user->status === 'active' ? 'bg-emerald-900/50 text-emerald-400' : 'bg-rose-900/50 text-rose-400' }}">
                            {{ ucfirst($user->status ?? 'active') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-auto pt-4 border-t border-slate-800">
                    <button @click="showEditModal = true; editUser = {{ $user->toJson() }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-medium py-2 rounded-xl text-xs transition-colors">
                        Edit
                    </button>
                    <form action="{{ route('company.users.toggleStatus', $user->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full font-medium py-2 rounded-xl text-xs transition-colors {{ $user->status === 'active' ? 'bg-rose-900/30 text-rose-400 hover:bg-rose-900/50' : 'bg-emerald-900/30 text-emerald-400 hover:bg-emerald-900/50' }}">
                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('company.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-400 border border-slate-700 transition" title="Delete User">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-slate-400">
                No users found.
            </div>
            @endforelse
        </div>
        
        <div class="mt-8">
            {{ $users->links() ?? '' }}
        </div>
    </div>

    <!-- Add Modal -->
    <div x-cloak x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showAddModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showAddModal = false"></div>
        
        <!-- Modal Dialog Box -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="showAddModal = false">
            <div x-show="showAddModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <form action="{{ route('company.users.store') }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900">
                        <h3 class="text-lg font-bold text-white">Add New User</h3>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        @if($errors->any())
                            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
                                <div class="font-bold mb-1">Please fix the following errors:</div>
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. John Doe" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="e.g. john@solvia.id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password *</label>
                            <input type="password" name="password" required minlength="6" placeholder="Min. 6 characters" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Role *</label>
                                <select name="role" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                    <option value="super_admin">Super Admin</option>
                                    <option value="content_creator">Content Creator</option>
                                    <option value="designer">Designer</option>
                                    <option value="frontend_developer">Frontend Developer</option>
                                    <option value="backend_developer">Backend Developer</option>
                                    <option value="iot_engineer">IoT Engineer</option>
                                    <option value="viewer" selected>Viewer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status *</label>
                                <select name="status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Department</label>
                                <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Engineering" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +62 812..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Team Assignment</label>
                            <select name="team_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                <option value="">-- No Team (Unassigned) --</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-950/80 border-t border-slate-800 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-colors">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showEditModal = false"></div>
        
        <!-- Modal Dialog Box -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="showEditModal = false">
            <div x-show="showEditModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <form :action="`/company/users/${editUser?.id}`" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900">
                        <h3 class="text-lg font-bold text-white">Edit User</h3>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name *</label>
                            <input type="text" name="name" :value="editUser?.name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address *</label>
                            <input type="email" name="email" :value="editUser?.email" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">New Password <span class="text-slate-500 font-normal">(Leave blank to keep current)</span></label>
                            <input type="password" name="password" minlength="6" placeholder="Leave empty to keep unchanged" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Role *</label>
                                <select name="role" :value="editUser?.role" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                    <option value="super_admin">Super Admin</option>
                                    <option value="content_creator">Content Creator</option>
                                    <option value="designer">Designer</option>
                                    <option value="frontend_developer">Frontend Developer</option>
                                    <option value="backend_developer">Backend Developer</option>
                                    <option value="iot_engineer">IoT Engineer</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status *</label>
                                <select name="status" :value="editUser?.status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Department</label>
                                <input type="text" name="department" :value="editUser?.department" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone</label>
                                <input type="text" name="phone" :value="editUser?.phone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Team Assignment</label>
                            <select name="team_id" :value="editUser?.team_id || ''" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                <option value="">-- No Team (Unassigned) --</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-950/80 border-t border-slate-800 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-colors">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
