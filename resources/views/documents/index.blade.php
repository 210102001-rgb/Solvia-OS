@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ uploadModal: {{ $errors->any() ? 'true' : 'false' }} }">
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

    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Documents</h1>
            <p class="mt-2 text-sm text-slate-400">Manage, search, and securely store your project files.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button @click="uploadModal = true" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-950 sm:w-auto">
                <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                Upload Document
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 mb-8 flex flex-col sm:flex-row gap-4 items-center">
        <form method="GET" action="{{ route('documents.index') }}" class="w-full flex flex-col sm:flex-row gap-4 items-center">
            <div class="w-full sm:w-auto flex-1">
                <input type="text" name="search" placeholder="Search filenames..." value="{{ $filters['search'] ?? '' }}" class="block w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-full sm:w-auto">
                <select name="type" class="block w-full rounded-xl border-slate-700 bg-slate-950 text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="project" {{ ($filters['type'] ?? '') == 'project' ? 'selected' : '' }}>Projects</option>
                    <option value="task" {{ ($filters['type'] ?? '') == 'task' ? 'selected' : '' }}>Tasks</option>
                    <option value="general" {{ ($filters['type'] ?? '') == 'general' ? 'selected' : '' }}>General</option>
                </select>
            </div>
            <div class="w-full sm:w-auto flex items-center space-x-2">
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="block w-full rounded-xl border-slate-700 bg-slate-950 text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-950 w-full sm:w-auto">
                Filter
            </button>
        </form>
    </div>

    <!-- Document Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($documents as $document)
        @php
            $ext = strtolower($document->file_type ?? pathinfo($document->file_path ?? '', PATHINFO_EXTENSION));
            $sizeKb = $document->file_size ? round($document->file_size / 1024, 1) : 0;
        @endphp
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 flex flex-col hover:border-indigo-500/50 transition-colors">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3 truncate">
                    @if($ext === 'pdf')
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
                        </div>
                    @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-500">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg>
                        </div>
                    @elseif(in_array($ext, ['doc', 'docx']))
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                        </div>
                    @else
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-400">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $document->title ?: basename($document->file_path ?? 'document') }}</p>
                        <p class="text-xs text-slate-400">{{ $sizeKb }} KB &nbsp;•&nbsp; {{ strtoupper($ext ?: 'file') }}</p>
                    </div>
                </div>
                <!-- Dropdown for Actions -->
                <div class="flex-shrink-0 ml-4 relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="text-slate-400 hover:text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" /></svg>
                    </button>
                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-36 rounded-xl bg-slate-800 border border-slate-700 shadow-lg py-1 z-10">
                        <a href="{{ route('documents.download', $document->id) }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">Download</a>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-rose-500 hover:bg-slate-700" onclick="return confirm('Delete this document?')">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex-1">
                @if($document->documentable_type)
                <p class="text-xs text-slate-400">
                    Linked to: <span class="text-indigo-400">{{ class_basename($document->documentable_type) }} #{{ $document->documentable_id }}</span>
                </p>
                @endif
            </div>
            
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500 pt-4 border-t border-slate-800">
                <span>By {{ $document->uploader->name ?? 'User' }}</span>
                <span>{{ $document->created_at->format('M d, Y') }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-900 rounded-2xl border border-slate-800 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <h3 class="mt-2 text-sm font-medium text-white">No documents found</h3>
            <p class="mt-1 text-sm text-slate-400">Get started by uploading a new document.</p>
        </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $documents->links() ?? '' }}
    </div>

    <!-- Upload Modal -->
    <div x-show="uploadModal" class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="uploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="uploadModal" @click.away="uploadModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-white mb-4" id="modal-title">Upload Document</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-400">File</label>
                                    <input type="file" name="file" required class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Title (Optional)</label>
                                    <input type="text" name="title" placeholder="Leave empty to use original filename" class="block w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Category *</label>
                                    <select name="category" required class="block w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="general" selected>General</option>
                                        <option value="contract">Contract</option>
                                        <option value="invoice">Invoice</option>
                                        <option value="sop">SOP</option>
                                        <option value="policy">Policy</option>
                                        <option value="technical">Technical</option>
                                        <option value="specification">Specification</option>
                                    </select>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium text-slate-400">Related Type</label>
                                        <select name="documentable_type" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">None</option>
                                            <option value="App\Models\Project">Project</option>
                                            <option value="App\Models\Task">Task</option>
                                        </select>
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium text-slate-400">Related ID</label>
                                        <input type="number" name="documentable_id" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400">Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 sm:ml-3 sm:w-auto sm:text-sm">Upload File</button>
                            <button type="button" @click="uploadModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-base font-medium text-slate-300 shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
