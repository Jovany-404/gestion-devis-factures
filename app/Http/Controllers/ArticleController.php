<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\CompanyProfile;
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

        return view('articles.index', [
            'articles' => $articles,
            'search' => $search,
            'profile' => CompanyProfile::current(),
        ]);
    }

    public function create(): View
    {
        return view('articles.create', [
            'article' => new Article,
            'profile' => CompanyProfile::current(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = Article::create($this->articleAttributes($request));

        return to_route('articles.index')->with('success', "L’article « {$article->name} » a été ajouté au catalogue.");
    }

    public function edit(Article $article): View
    {
        return view('articles.edit', [
            'article' => $article,
            'profile' => CompanyProfile::current(),
        ]);
    }

    public function update(StoreArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($this->articleAttributes($request));

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

    /**
     * @return array<string, mixed>
     */
    private function articleAttributes(StoreArticleRequest $request): array
    {
        $attributes = $request->validated();

        if (CompanyProfile::current()->currency === 'XOF') {
            $attributes['unit_price'] = round((float) $attributes['unit_price'], 0, PHP_ROUND_HALF_UP);
        }

        return $attributes;
    }
}
