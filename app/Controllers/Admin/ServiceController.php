<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Offerings;
use PDOException;

final class ServiceController
{
    private const FIELDS = [
        'title', 'slug', 'summary', 'content', 'status', 'published_at',
        'featured_image', 'cta_label', 'cta_url', 'seo_title', 'seo_description',
    ];

    public function index(): Response
    {
        $rows = Offerings::all();
        $last = count($rows) - 1;

        foreach ($rows as $i => &$row) {
            $id = (int) $row['id'];
            $row['can_delete'] = $this->canDelete($row);
            $row['edit_url'] = route('admin.services.edit', ['id' => $id]);
            $row['delete_url'] = route('admin.services.delete', ['id' => $id]);
            $row['move_url'] = route('admin.services.move', ['id' => $id]);
            $row['is_first'] = $i === 0;
            $row['is_last'] = $i === $last;
        }
        unset($row);

        return view('admin/services/index', ['rows' => $rows]);
    }

    public function create(): Response
    {
        $values = $this->flashedOld() + ['status' => 'draft', 'is_featured' => '0'];

        return view('admin/services/form', [
            'service' => null,
            'values' => $this->prepare($values),
            'errors' => $this->flashedErrors(),
            'reps' => Offerings::repeaters(),
            'ctaFields' => Offerings::ctaFields(),
            'canPublish' => can('content.publish'),
        ]);
    }

    public function store(Request $request): Response
    {
        $input = $this->input($request);
        [$values, $errors] = Offerings::validate($input, null, can('content.publish'));

        if ($errors === []) {
            try {
                $id = Offerings::create($values, $this->userId());
                Session::flash('success', 'Layanan dibuat.');

                return redirect_route('admin.services.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai layanan lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.services.create'));
    }

    public function edit(int $id): Response
    {
        $service = Offerings::find($id) ?? abort(404);

        return view('admin/services/form', [
            'service' => $service,
            'values' => $this->prepare($this->flashedOld() + $this->formValues($service)),
            'errors' => $this->flashedErrors(),
            'reps' => Offerings::repeaters(),
            'ctaFields' => Offerings::ctaFields(),
            'canPublish' => can('content.publish'),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $service = Offerings::find($id) ?? abort(404);

        $input = $this->input($request);
        [$values, $errors] = Offerings::validate($input, $service, can('content.publish'));

        if ($errors === []) {
            try {
                Offerings::update($id, $values, $this->userId());
                Session::flash('success', 'Layanan disimpan.');

                return redirect_route('admin.services.edit', ['id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $errors['slug'] = 'Slug sudah dipakai layanan lain.';
            }
        }

        return $this->invalid($input, $errors, route('admin.services.edit', ['id' => $id]));
    }

    public function move(Request $request, int $id): Response
    {
        Offerings::find($id) ?? abort(404);

        $direction = $request->input('dir', '');
        if (is_string($direction) && in_array($direction, ['up', 'down'], true)) {
            Offerings::move($id, $direction);
        }

        return redirect_route('admin.services');
    }

    public function delete(int $id): Response
    {
        $service = Offerings::find($id) ?? abort(404);

        // Cek ulang kepemilikan di server
        if (!$this->canDelete($service)) {
            abort(403);
        }

        Offerings::delete($id);
        Session::flash('success', 'Layanan dihapus.');

        return redirect_route('admin.services');
    }

    private function canDelete(array $service): bool
    {
        if (can('content.delete_any')) {
            return true;
        }

        return can('content.delete_own')
            && $service['created_by'] !== null
            && (int) $service['created_by'] === $this->userId();
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

        foreach (array_keys(Offerings::repeaters()) as $key) {
            $value = $request->input($key, []);
            $out[$key] = is_array($value) ? $value : [];
        }

        return $out;
    }

    /** Nilai awal form dari baris database. */
    private function formValues(array $service): array
    {
        $date = $service['published_at'] !== null
            ? date('Y-m-d\TH:i', (int) strtotime((string) $service['published_at']))
            : '';

        return [
            'title' => (string) $service['title'],
            'slug' => (string) $service['slug'],
            'summary' => (string) $service['summary'],
            'content' => (string) $service['content'],
            'status' => (string) $service['status'],
            'published_at' => $date,
            'is_featured' => (int) $service['is_featured'] === 1 ? '1' : '0',
            'featured_image' => $service['featured_image_id'] !== null ? (string) $service['featured_image_id'] : '',
            'cta_label' => (string) $service['cta_label'],
            'cta_url' => (string) $service['cta_url'],
            'seo_title' => (string) $service['seo_title'],
            'seo_description' => (string) $service['seo_description'],
        ] + Offerings::details($service);
    }

    /** Pastikan daftar item berbentuk list baris string yang aman untuk form. */
    private function prepare(array $values): array
    {
        foreach (Offerings::repeaters() as $key => $rep) {
            $rows = [];
            $raw = is_array($values[$key] ?? null) ? array_values($values[$key]) : [];

            foreach (array_slice($raw, 0, (int) $rep['max']) as $item) {
                $row = [];
                foreach (array_keys($rep['fields']) as $field) {
                    $row[$field] = is_array($item) && is_string($item[$field] ?? null) ? $item[$field] : '';
                }
                $rows[] = $row;
            }

            $values[$key] = $rows;
        }

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