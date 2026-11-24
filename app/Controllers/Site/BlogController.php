<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Services\Blog;

final class BlogController
{
    public function index(Request $request): Response
    {
        return $this->listing($request, null, null);
    }

    public function category(Request $request, string $slug): Response
    {
        $category = Blog::category($slug) ?? abort(404);

        return $this->listing($request, $category, null);
    }

    public function tag(Request $request, string $slug): Response
    {
        $tag = Blog::tag($slug) ?? abort(404);

        return $this->listing($request, null, $tag);
    }

    public function show(string $slug): Response
    {
        $article = Blog::article($slug) ?? abort(404);

        $categoryId = $article['category_id'] !== null ? (int) $article['category_id'] : null;
        $tagIds = array_map(static fn (array $t): int => (int) $t['id'], $article['tags']);

        return view('site/blog/show', [
            'article' => $article,
            'minutes' => Blog::readingMinutes((string) $article['content']),
            'related' => Blog::related((int) $article['id'], $categoryId, $tagIds, 3),
        ]);
    }

    private function listing(Request $request, ?array $category, ?array $tag): Response
    {
        $categoryId = $category !== null ? (int) $category['id'] : null;
        $tagId = $tag !== null ? (int) $tag['id'] : null;

        $requested = max(1, (int) $request->query('halaman', 1));
        [$items, $total] = Blog::articles($categoryId, $tagId, $requested);

        $pages = max(1, (int) ceil($total / Blog::PER_PAGE));
        if ($requested > $pages) {
            abort(404);
        }

        return view('site/blog/index', [
            'items' => $items,
            'category' => $category,
            'tag' => $tag,
            'categories' => Blog::categories(),
            'tags' => Blog::tags(20),
            'total' => $total,
            'page' => $requested,
            'pages' => $pages,
        ]);
    }
}