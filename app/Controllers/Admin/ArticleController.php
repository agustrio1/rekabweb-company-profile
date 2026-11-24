<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ArticleTaxonomy;
use App\Services\Articles;
use PDOException;

final class ArticleController
{
    private const FIELDS = [
        'title', 'slug', 'category_id', 'author_id', 'excerpt', 'content', 'status',
        'published_at', 'featured_image', 'tags', 'seo_title', 'seo_description',
    ];

    public function index(Request $request): Response
    {
        $q = $request->query('q', '');
        $q = is_string($q) ? mb_substr(trim($q), 0, 100) : '';
        $status = $request->query('status', '');
        $status = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : '';

        $total = Articles::count($q, $status);
        $pages = max(1, (int) ceil($total / Articles::PER_PAGE));
        $page = min($pages, max(1, (int) $request->query('page', 1)));

        $rows = Articles::search($q, $status, $page);
        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $row['can_delete'] = $this->canDelete($row);
            $row['edit_url'] = route('admin.articles.edit', ['id' => $id]);
            $row['delete_url'] = route('admin.articles.delete', ['id' => $id]);
        }
        unset($row);

        return view('admin/articles/index', [
            'rows' => $rows,
            'q' => $q,
            'status' => $status,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function create(): Response
    {
        $values = $this->flashedOld() + [
            'status' => 'draft',
            'author_id' => (string) ($this->userId() ?? ''),
        ];

        return $this->form(null, $values);
    }

    public function store(Request $request): Response
    {
        $input = $this->input($request);
        [$values, $errors] = Articles::validate($input, null, $this->context());

        if ($errors === []) {
            try {
                $id = Articles::create($values, $this->userId());
                Session::flash('success', 'Artikel dibuat.');

                return redirect_route('admin.articles.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai artikel lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.articles.create'));
    }

    public function edit(int $id): Response
    {
        $article = Articles::find($id) ?? abort(404);

        return $this->form($article, $this->flashedOld() + $this->formValues($article));
    }

    public function update(Request $request, int $id): Response
    {
        $article = Articles::find($id) ?? abort(404);

        $input = $this->input($request);
        [$values, $errors] = Articles::validate($input, $article, $this->context());

        if ($errors === []) {
            try {
                Articles::update($id, $values, $this->userId());
                Session::flash('success', 'Artikel disimpan.');

                return redirect_route('admin.articles.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai artikel lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.articles.edit', ['id' => $id]));
    }

    public function delete(int $id): Response
    {
        $article = Articles::find($id) ?? abort(404);

        // Cek ulang kepemilikan di server
        if (!$this->canDelete($article)) {
            abort(403);
        }

        Articles::delete($id);
        Session::flash('success', 'Artikel dihapus.');

        return redirect_route('admin.articles');
    }

    private function form(?array $article, array $values): Response
    {
        $canAuthor = can('users.manage');
        $currentAuthor = $article !== null && $article['author_id'] !== null ? (int) $article['author_id'] : null;

        return view('admin/articles/form', [
            'article' => $article,
            'values' => $values,
            'errors' => $this->flashedErrors(),
            'categories' => ArticleTaxonomy::categoryOptions(),
            'popularTags' => ArticleTaxonomy::popularTags(20),
            'authors' => $canAuthor ? Articles::authors($currentAuthor) : [],
            'canAuthor' => $canAuthor,
            'canPublish' => can('content.publish'),
            'maxTags' => Articles::MAX_TAGS,
        ]);
    }

    /** @return array{can_publish: bool, can_author: bool, user_id: ?int} */
    private function context(): array
    {
        return [
            'can_publish' => can('content.publish'),
            'can_author' => can('users.manage'),
            'user_id' => $this->userId(),
        ];
    }

    private function canDelete(array $article): bool
    {
        if (can('content.delete_any')) {
            return true;
        }

        return can('content.delete_own')
            && $article['created_by'] !== null
            && (int) $article['created_by'] === $this->userId();
    }

    private function userId(): ?int
    {
        return (int) (auth_user()['id'] ?? 0) ?: null;
    }

    /** @return array<string, string> */
    private function input(Request $request): array
    {
        $out = [];
        foreach (self::FIELDS as $key) {
            $value = $request->input($key, '');
            $out[$key] = is_string($value) ? $value : '';
        }

        return $out;
    }

    /** Nilai awal form dari baris database. */
    private function formValues(array $article): array
    {
        $date = $article['published_at'] !== null
            ? date('Y-m-d\TH:i', (int) strtotime((string) $article['published_at']))
            : '';

        return [
            'title' => (string) $article['title'],
            'slug' => (string) $article['slug'],
            'category_id' => $article['category_id'] !== null ? (string) $article['category_id'] : '',
            'author_id' => $article['author_id'] !== null ? (string) $article['author_id'] : '',
            'excerpt' => (string) $article['excerpt'],
            'content' => (string) $article['content'],
            'status' => (string) $article['status'],
            'published_at' => $date,
            'featured_image' => $article['featured_image_id'] !== null ? (string) $article['featured_image_id'] : '',
            'tags' => implode(', ', Articles::tagNames((int) $article['id'])),
            'seo_title' => (string) $article['seo_title'],
            'seo_description' => (string) $article['seo_description'],
        ];
    }

    private function invalid(array $input, array $errors, string $back): Response
    {
        Session::flash('error', 'Periksa kembali isian yang ditandai.');
        Session::flash('errors', $errors);
        Session::flash('old', $input);

        return Response::redirect($back);
    }

    private function flashedOld(): array
    {
        $old = Session::pullFlash('old');

        return is_array($old) ? $old : [];
    }

    private function flashedErrors(): array
    {
        $errors = Session::pullFlash('errors');

        return is_array($errors) ? $errors : [];
    }
}