<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $articles = Article::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('articles.index', compact('articles', 'search'));
    }

    public function create(): View
    {
        return view('articles.create', ['article' => new Article]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = Article::create($request->validated());

        return to_route('articles.index')->with('success', "L’article « {$article->name} » a été ajouté au catalogue.");
    }

    public function edit(Article $article): View
    {
        return view('articles.edit', compact('article'));
    }

    public function update(StoreArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($request->validated());

        return to_route('articles.index')->with('success', 'L’article a été mis à jour.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->update(['is_active' => false]);

        return to_route('articles.index')->with(
            'success',
            'L’article a été archivé ; son historique reste intact dans les documents existants.'
        );
    }
}
