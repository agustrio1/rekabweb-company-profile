<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Faqs;

final class FaqController
{
    private const FIELDS = ['question', 'answer', 'category', 'status'];

    public function index(): Response
    {
        $rows = Faqs::all();
        $last = count($rows) - 1;

        foreach ($rows as $i => &$row) {
            $id = (int) $row['id'];
            $row['can_delete'] = $this->canDelete($row);
            $row['edit_url'] = route('admin.faqs.edit', ['id' => $id]);
            $row['delete_url'] = route('admin.faqs.delete', ['id' => $id]);
            $row['move_url'] = route('admin.faqs.move', ['id' => $id]);
            $row['is_first'] = $i === 0;
            $row['is_last'] = $i === $last;
        }
        unset($row);

        return view('admin/faqs/index', ['rows' => $rows]);
    }

    public function create(): Response
    {
        return $this->form(null, $this->flashedOld() + ['status' => 'draft']);
    }

    public function store(Request $request): Response
    {
        $input = $this->input($request);
        [$values, $errors] = Faqs::validate($input, null, can('content.publish'));

        if ($errors === []) {
            $id = Faqs::create($values, $this->userId());
            Session::flash('success', 'FAQ dibuat.');

            return redirect_route('admin.faqs.edit', ['id' => $id]);
        }

        return $this->invalid($input, $errors, route('admin.faqs.create'));
    }

    public function edit(int $id): Response
    {
        $row = Faqs::find($id) ?? abort(404);

        return $this->form($row, $this->flashedOld() + [
            'question' => (string) $row['question'],
            'answer' => (string) $row['answer'],
            'category' => (string) $row['category'],
            'status' => (string) $row['status'],
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $row = Faqs::find($id) ?? abort(404);

        $input = $this->input($request);
        [$values, $errors] = Faqs::validate($input, $row, can('content.publish'));

        if ($errors === []) {
            Faqs::update($id, $values, $this->userId());
            Session::flash('success', 'FAQ disimpan.');

            return redirect_route('admin.faqs.edit', ['id' => $id]);
        }

        return $this->invalid($input, $errors, route('admin.faqs.edit', ['id' => $id]));
    }

    public function move(Request $request, int $id): Response
    {
        Faqs::find($id) ?? abort(404);

        $direction = $request->input('dir', '');
        if (is_string($direction) && in_array($direction, ['up', 'down'], true)) {
            Faqs::move($id, $direction);
        }

        return redirect_route('admin.faqs');
    }

    public function delete(int $id): Response
    {
        $row = Faqs::find($id) ?? abort(404);

        // Cek ulang kepemilikan di server
        if (!$this->canDelete($row)) {
            abort(403);
        }

        Faqs::delete($id);
        Session::flash('success', 'FAQ dihapus.');

        return redirect_route('admin.faqs');
    }

    private function form(?array $row, array $values): Response
    {
        return view('admin/faqs/form', [
            'item' => $row,
            'values' => $values,
            'errors' => $this->flashedErrors(),
            'fields' => Faqs::fields(),
            'canPublish' => can('content.publish'),
        ]);
    }

    private function canDelete(array $row): bool
    {
        if (can('content.delete_any')) {
            return true;
        }

        return can('content.delete_own')
            && $row['created_by'] !== null
            && (int) $row['created_by'] === $this->userId();
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