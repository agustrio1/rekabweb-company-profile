<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Services\Catalog;

final class ProjectController
{
    public function index(Request $request): Response
    {
        return $this->listing($request, null);
    }

    public function category(Request $request, string $slug): Response
    {
        $category = Catalog::projectCategory($slug) ?? abort(404);

        return $this->listing($request, $category);
    }

    public function show(string $slug): Response
    {
        $project = Catalog::project($slug) ?? abort(404);

        $categoryId = $project['category_id'] !== null ? (int) $project['category_id'] : null;

        return view('site/projects/show', [
            'project' => $project,
            'others' => Catalog::otherProjects((int) $project['id'], $categoryId, 3),
        ]);
    }

    private function listing(Request $request, ?array $category): Response
    {
        $categoryId = $category !== null ? (int) $category['id'] : null;

        $requested = max(1, (int) $request->query('halaman', 1));
        [$items, $total] = Catalog::projects($categoryId, $requested);

        $pages = max(1, (int) ceil($total / Catalog::PROJECTS_PER_PAGE));
        if ($requested > $pages) {
            abort(404);
        }

        return view('site/projects/index', [
            'items' => $items,
            'category' => $category,
            'categories' => Catalog::projectCategories(),
            'total' => $total,
            'page' => $requested,
            'pages' => $pages,
        ]);
    }
}