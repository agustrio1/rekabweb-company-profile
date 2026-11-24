<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Pages;
use PDOException;

final class PageController
{
    private const FIELDS = ['title', 'slug', 'content', 'status', 'published_at', 'featured_image', 'seo_title', 'seo_description'];

    public function index(Request $request): Response
    {
        $q = $request->query('q', '');
        $q = is_string($q) ? mb_substr(trim($q), 0, 100) : '';
        $status = $request->query('status', '');
        $status = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : '';

        $total = Pages::count($q, $status);
        $pages = max(1, (int) ceil($total / Pages::PER_PAGE));
        $page = min($pages, max(1, (int) $request->query('page', 1)));

        $rows = Pages::search($q, $status, $page);
        foreach ($rows as &$row) {
            $row['can_delete'] = $this->canDelete($row);
            $row['delete_url'] = route('admin.pages.delete', ['id' => (int) $row['id']]);
            $row['edit_url'] = route('admin.pages.edit', ['id' => (int) $row['id']]);
        }
        unset($row);

        return view('admin/pages/index', [
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
        return view('admin/pages/form', [
            'page' => null,
            'values' => $this->flashedOld() + ['status' => 'draft'],
            'errors' => $this->flashedErrors(),
            'canPublish' => can('content.publish'),
        ]);
    }

    public function store(Request $request): Response
    {
        $input = $this->input($request);
        [$values, $errors] = Pages::validate($input, null, can('content.publish'));

        if ($errors === []) {
            try {
                $id = Pages::create($values, $this->userId());
                Session::flash('success', 'Halaman dibuat.');

                return redirect_route('admin.pages.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai halaman lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.pages.create'));
    }

    public function edit(int $id): Response
    {
        $page = Pages::find($id) ?? abort(404);

        return view('admin/pages/form', [
            'page' => $page,
            'values' => $this->flashedOld() + $this->formValues($page),
            'errors' => $this->flashedErrors(),
            'canPublish' => can('content.publish'),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $page = Pages::find($id) ?? abort(404);

        $input = $this->input($request);
        [$values, $errors] = Pages::validate($input, $page, can('content.publish'));

        if ($errors === []) {
            try {
                Pages::update($id, $values, $this->userId());
                Session::flash('success', 'Halaman disimpan.');

                return redirect_route('admin.pages.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai halaman lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.pages.edit', ['id' => $id]));
    }

    public function delete(int $id): Response
    {
        $page = Pages::find($id) ?? abort(404);

        // Cek ulang kepemilikan di server
        if (!$this->canDelete($page)) {
            abort(403);
        }

        Pages::delete($id);
        Session::flash('success', 'Halaman dihapus.');

        return redirect_route('admin.pages');
    }

    private function canDelete(array $page): bool
    {
        if (can('content.delete_any')) {
            return true;
        }

        return can('content.delete_own')
            && $page['created_by'] !== null
            && (int) $page['created_by'] === $this->userId();
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
    private function formValues(array $page): array
    {
        $date = $page['published_at'] !== null
            ? date('Y-m-d\TH:i', (int) strtotime((string) $page['published_at']))
            : '';

        return [
            'title' => (string) $page['title'],
            'slug' => (string) $page['slug'],
            'content' => (string) $page['content'],
            'status' => (string) $page['status'],
            'published_at' => $date,
            'featured_image' => $page['featured_image_id'] !== null ? (string) $page['featured_image_id'] : '',
            'seo_title' => (string) $page['seo_title'],
            'seo_description' => (string) $page['seo_description'],
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