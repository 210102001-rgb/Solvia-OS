<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\KnowledgeBase;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Document::with('uploader');

        if (!$user->isSuperAdmin()) {
            $query->where('is_restricted', false);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $documents = $query->orderByDesc('created_at')->paginate(20);
        $filters   = $request->only(['category', 'search']);

        return view('documents.index', compact('documents', 'filters'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('category')) {
            $request->merge(['category' => 'general']);
        }
        if (!$request->filled('title') && $request->hasFile('file')) {
            $request->merge(['title' => pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME)]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:contract,invoice,sop,policy,technical,specification,general',
            'file' => 'required|file|max:20480', // 20MB limit
            'is_restricted' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $doc = Document::create([
            'title' => $data['title'],
            'category' => $data['category'],
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
            'uploader_id' => Auth::id(),
            'is_restricted' => $request->boolean('is_restricted', false),
        ]);

        AuditLogger::log('create', 'Document', $doc->id, null, ['title' => $doc->title], "Document uploaded: {$doc->title}");
        return back()->with('success', "Document '{$doc->title}' uploaded successfully.");
    }

    public function knowledgeBase(Request $request)
    {
        $query = KnowledgeBase::with('author')->where('is_published', true);
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->orderByDesc('created_at')->paginate(15);
        return view('documents.knowledge-base', compact('articles'));
    }

    public function showArticle(KnowledgeBase $article)
    {
        return view('documents.kb-show', compact('article'));
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:sop,technical_doc,brand_guideline,faq,tutorial,internal_policy,project_guide,development_guide',
            'content' => 'required|string',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . rand(100, 999);
        $data['author_id'] = Auth::id();
        $data['is_published'] = true;

        $article = KnowledgeBase::create($data);
        AuditLogger::log('create', 'KnowledgeBase', $article->id, null, ['title' => $article->title], "Knowledge base article created: {$article->title}");

        return back()->with('success', "Knowledge base article published.");
    }

    public function destroyArticle(KnowledgeBase $article)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }
        $title = $article->title;
        $article->delete();
        AuditLogger::log('delete', 'KnowledgeBase', $article->id, null, null, "Article deleted: {$title}");
        return redirect()->route('documents.kb')->with('success', "Article '{$title}' deleted successfully.");
    }
}
