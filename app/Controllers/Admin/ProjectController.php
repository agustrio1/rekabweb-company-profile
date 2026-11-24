<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ProjectCategories;
use App\Services\Projects;
use PDOException;

final class ProjectController
{
    private const FIELDS = [
        'title', 'slug', 'category_id', 'summary', 'content', 'status', 'published_at',
        'featured_image', 'completed_year', 'client_name', 'location',
        'challenge', 'solution', 'results', 'seo_title', 'seo_description',
    ];

    public function index(): Response
    {
        $rows = Projects::all();
        $last = count($rows) - 1;

        foreach ($rows as $i => &$row) {
            $id = (int) $row['id'];
            $row['can_delete'] = $this->canDelete($row);
            $row['edit_url'] = route('admin.projects.edit', ['id' => $id]);
            $row['delete_url'] = route('admin.projects.delete', ['id' => $id]);
            $row['move_url'] = route('admin.projects.move', ['id' => $id]);
            $row['is_first'] = $i === 0;
            $row['is_last'] = $i === $last;
        }
        unset($row);

        return view('admin/projects/index', ['rows' => $rows]);
    }

    public function create(): Response
    {
        $values = $this->flashedOld() + ['status' => 'draft', 'is_featured' => '0', 'show_client' => '0'];

        return $this->form(null, $values);
    }

    public function store(Request $request): Response
    {
        $input = $this->input($request);
        [$values, $errors] = Projects::validate($input, null, can('content.publish'));

        if ($errors === []) {
            try {
                $id = Projects::create($values, $this->userId());
                Session::flash('success', 'Proyek dibuat.');

                return redirect_route('admin.projects.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai proyek lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.projects.create'));
    }

    public function edit(int $id): Response
    {
        $project = Projects::find($id) ?? abort(404);

        return $this->form($project, $this->flashedOld() + $this->formValues($project));
    }

    public function update(Request $request, int $id): Response
    {
        $project = Projects::find($id) ?? abort(404);

        $input = $this->input($request);
        [$values, $errors] = Projects::validate($input, $project, can('content.publish'));

        if ($errors === []) {
            try {
                Projects::update($id, $values, $this->userId());
                Session::flash('success', 'Proyek disimpan.');

                return redirect_route('admin.projects.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai proyek lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.projects.edit', ['id' => $id]));
    }

    public function move(Request $request, int $id): Response
    {
        Projects::find($id) ?? abort(404);

        $direction = $request->input('dir', '');
        if (is_string($direction) && in_array($direction, ['up', 'down'], true)) {
            Projects::move($id, $direction);
        }

        return redirect_route('admin.projects');
    }

    public function delete(int $id): Response
    {
        $project = Projects::find($id) ?? abort(404);

        // Cek ulang kepemilikan di server
        if (!$this->canDelete($project)) {
            abort(403);
        }

        Projects::delete($id);
        Session::flash('success', 'Proyek dihapus.');

        return redirect_route('admin.projects');
    }

    private function form(?array $project, array $values): Response
    {
        return view('admin/projects/form', [
            'project' => $project,
            'values' => $this->prepare($values),
            'errors' => $this->flashedErrors(),
            'categories' => ProjectCategories::options(),
            'textFields' => Projects::textFields(),
            'maxGallery' => Projects::MAX_GALLERY,
            'canPublish' => can('content.publish'),
        ]);
    }

    private function canDelete(array $project): bool
    {
        if (can('content.delete_any')) {
            return true;
        }

        return can('content.delete_own')
            && $project['created_by'] !== null
            && (int) $project['created_by'] === $this->userId();
    }

    private function userId(): ?int
    {
        return (int) (auth_user()['id'] ?? 0) ?: null;
    }

    /** @return array<string, mixed> */
    private function input(Request $request): array
    {
        $out = [];
        foreach (self::FIELDS as $key) {
            $value = $request->input($key, '');
            $out[$key] = is_string($value) ? $value : '';
        }

        $out['is_featured'] = $request->input('is_featured', '') === '1' ? '1' : '0';
        $out['show_client'] = $request->input('show_client', '') === '1' ? '1' : '0';

        $gallery = $request->input('gallery', []);
        $out['gallery'] = is_array($gallery) ? $gallery : [];

        return $out;
    }

    /** Nilai awal form dari baris database. */
    private function formValues(array $project): array
    {
        $date = $project['published_at'] !== null
            ? date('Y-m-d\TH:i', (int) strtotime((string) $project['published_at']))
            : '';

        return [
            'title' => (string) $project['title'],
            'slug' => (string) $project['slug'],
            'category_id' => $project['category_id'] !== null ? (string) $project['category_id'] : '',
            'summary' => (string) $project['summary'],
            'content' => (string) $project['content'],
            'status' => (string) $project['status'],
            'published_at' => $date,
            'is_featured' => (int) $project['is_featured'] === 1 ? '1' : '0',
            'show_client' => (int) $project['show_client'] === 1 ? '1' : '0',
            'featured_image' => $project['featured_image_id'] !== null ? (string) $project['featured_image_id'] : '',
            'completed_year' => $project['completed_year'] !== null ? (string) $project['completed_year'] : '',
            'client_name' => (string) $project['client_name'],
            'location' => (string) $project['location'],
            'challenge' => (string) $project['challenge'],
            'solution' => (string) $project['solution'],
            'results' => (string) $project['results'],
            'seo_title' => (string) $project['seo_title'],
            'seo_description' => (string) $project['seo_description'],
            'gallery' => Projects::galleryIds((int) $project['id']),
        ];
    }

    /** Ubah daftar ID galeri menjadi item tampil (id + url) untuk form. */
    private function prepare(array $values): array
    {
        $ids = [];
        foreach (is_array($values['gallery'] ?? null) ? $values['gallery'] : [] as $g) {
            if ((is_string($g) || is_int($g)) && ctype_digit((string) $g)) {
                $ids[] = (int) $g;
            }
        }

        $values['gallery_items'] = Projects::galleryItems(array_slice($ids, 0, Projects::MAX_GALLERY));

        return $values;
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