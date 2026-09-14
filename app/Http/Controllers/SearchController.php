<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function __construct(protected GlobalSearchService $searchService) {}

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $results = $this->searchService->search($query, Auth::user());
        return response()->json($results);
    }
}
