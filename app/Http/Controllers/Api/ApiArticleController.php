<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ApiArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Article::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(50);

        return response()->json([
            'data' => $articles->items(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}
