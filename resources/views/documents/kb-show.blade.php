@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-2">
            <li>
                <div class="flex items-center">
                    <a href="{{ route('documents.kb') }}" class="text-sm font-medium text-slate-400 hover:text-white">Knowledge Base</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 flex-shrink-0 text-slate-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    <a href="{{ route('documents.kb', ['category' => $article->category]) }}" class="ml-2 text-sm font-medium text-slate-400 hover:text-white">{{ $article->category }}</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 flex-shrink-0 text-slate-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    <span class="ml-2 text-sm font-medium text-slate-200 truncate max-w-xs" aria-current="page">{{ $article->title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 sm:p-8 lg:p-10 shadow">
                <!-- Header -->
                <div class="mb-8 border-b border-slate-800 pb-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center rounded-md bg-indigo-500/10 px-3 py-1 text-sm font-medium text-indigo-400 ring-1 ring-inset ring-indigo-500/20">
                            {{ $article->category }}
                        </span>
                        
                        @if(auth()->user()->isAdmin() ?? false)
                        <div class="flex gap-2">
                            <form action="{{ route('documents.kb.destroy', $article->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this article?')" class="inline-flex items-center rounded-lg bg-rose-500/10 px-3 py-1.5 text-sm font-medium text-rose-500 hover:bg-rose-500/20 transition-colors border border-rose-500/20">
                                    Delete
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    
                    <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">{{ $article->title }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-400">
                        <div class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            {{ $article->author->name ?? 'Admin' }}
                        </div>
                        <div class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Last updated {{ $article->updated_at->format('F j, Y') }}
                        </div>
                    </div>
                </div>
                
                <!-- Article Content -->
                <div class="prose prose-invert prose-indigo max-w-none text-slate-300">
                    {!! nl2br(e($article->content)) !!}
                </div>
                
                <!-- Tags -->
                @if($article->tags)
                <div class="mt-10 pt-6 border-t border-slate-800">
                    <h4 class="text-sm font-medium text-white mb-3">Tags:</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $article->tags) as $tag)
                        <a href="{{ route('kb.index', ['tag' => trim($tag)]) }}" class="inline-flex items-center rounded-lg bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors border border-slate-700">
                            #{{ trim($tag) }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('kb.index') }}" class="text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                    &larr; Back to Knowledge Base
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:w-80 flex-shrink-0 space-y-6">
            @if(count($related ?? []) > 0)
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 sticky top-6">
                <h3 class="text-sm font-medium text-white uppercase tracking-wider mb-4 flex items-center">
                    <svg class="mr-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    Related Articles
                </h3>
                <ul role="list" class="divide-y divide-slate-800">
                    @foreach($related as $rel)
                    <li class="py-3">
                        <a href="{{ route('kb.show', $rel->id) }}" class="block group">
                            <h4 class="text-sm font-medium text-slate-300 group-hover:text-indigo-400 transition-colors line-clamp-2 mb-1">
                                {{ $rel->title }}
                            </h4>
                            <p class="text-xs text-slate-500">
                                {{ $rel->views_count ?? 0 }} views
                            </p>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
