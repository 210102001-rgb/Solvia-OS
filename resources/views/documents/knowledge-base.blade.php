@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showModal: false }">
    <!-- Page Header & Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Knowledge Base</h1>
            <p class="mt-2 text-sm text-slate-400">Find tutorials, guides, and documentation.</p>
        </div>
        <div class="flex-1 md:max-w-md w-full">
            <form action="{{ route('documents.kb') }}" method="GET" class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-xl border-slate-700 bg-slate-900 py-2.5 pl-10 pr-3 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm" placeholder="Search articles...">
            </form>
        </div>
        @if(auth()->user()->isAdmin() ?? false)
        <div>
            <button @click="showModal = true" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-950 w-full md:w-auto">
                <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                New Article
            </button>
        </div>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 sticky top-6">
                <h3 class="text-sm font-medium text-white uppercase tracking-wider mb-4">Categories</h3>
                <nav class="space-y-1">
                    <a href="{{ route('documents.kb') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ !($selectedCategory ?? '') ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="truncate">All Categories</span>
                    </a>
                    @foreach($categories ?? [] as $category)
                    <a href="{{ route('documents.kb', ['category' => $category]) }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ ($selectedCategory ?? '') == $category ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="truncate">{{ $category }}</span>
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Articles Grid -->
        <div class="flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($articles ?? [] as $article)
                <a href="{{ route('documents.kb.show', $article->slug) }}" class="flex flex-col bg-slate-900 rounded-2xl border border-slate-800 p-6 hover:border-indigo-500/50 hover:bg-slate-800/50 transition-all shadow-sm">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center rounded-md bg-slate-800 px-2 py-1 text-xs font-medium text-slate-300 ring-1 ring-inset ring-slate-700">
                                {{ $article->category }}
                            </span>
                            <span class="flex items-center text-xs text-slate-500">
                                <svg class="mr-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                {{ $article->views_count ?? 0 }}
                            </span>
                        </div>
                        <h3 class="text-lg font-medium text-white mb-2 line-clamp-2">{{ $article->title }}</h3>
                        <p class="text-sm text-slate-400 line-clamp-3 mb-4">
                            {{ Str::limit(strip_tags($article->content), 150) }}
                        </p>
                        @if($article->tags)
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach(explode(',', $article->tags) as $tag)
                            <span class="text-xs text-indigo-400">#{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center">
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            {{ $article->author->name ?? 'Admin' }}
                        </div>
                        <span>{{ $article->updated_at->format('M d, Y') }}</span>
                    </div>
                </a>
                @empty
                <div class="col-span-full text-center py-12 bg-slate-900 rounded-2xl border border-slate-800">
                    <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    <h3 class="mt-2 text-sm font-medium text-white">No articles found</h3>
                    <p class="mt-1 text-sm text-slate-400">Try adjusting your search or category filter.</p>
                </div>
                @endforelse
            </div>
            
            <div class="mt-8">
                {{ $articles->links() ?? '' }}
            </div>
        </div>
    </div>

    <!-- Create Article Modal -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-xl">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-5">
                    <h3 class="text-lg font-bold text-white">Create Knowledge Base Article</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>
                <form action="{{ route('documents.kb.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Article Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Standard Operating Procedure for API Deployment" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Category *</label>
                        <select name="category" required class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="sop">SOP</option>
                            <option value="technical_doc">Technical Doc</option>
                            <option value="brand_guideline">Brand Guideline</option>
                            <option value="faq">FAQ</option>
                            <option value="tutorial">Tutorial</option>
                            <option value="internal_policy">Internal Policy</option>
                            <option value="project_guide">Project Guide</option>
                            <option value="development_guide">Development Guide</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Content *</label>
                        <textarea name="content" rows="8" required placeholder="Write article content here (Markdown or text)..." class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-indigo-500 font-mono"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30">Publish Article</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
