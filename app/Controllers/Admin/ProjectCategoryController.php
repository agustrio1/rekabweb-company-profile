<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ProjectCategories;
use PDOException;

final class ProjectCategoryController
{
    public function index(): Response
    {
        return view('admin/projects/categories', [
            'rows' => ProjectCategories::all(),
            'canDelete' => can('content.delete_any'),
            'max' => ProjectCategories::MAX,
        ]);
    }

    public function store(Request $request): Response
    {
        if (ProjectCategories::count() >= ProjectCategories::MAX) {
            Session::flash('error', 'Maksimal ' . ProjectCategories::MAX . ' kategori.');

            return redirect_route('admin.project-categories');
        }

        [$name, $error] = ProjectCategories::validateName($request->input('name', ''), null);

        if ($error === null) {
            try {
                ProjectCategories::create($name);
                Session::flash('success', 'Kategori "' . $name . '" dibuat.');

                return redirect_route('admin.project-categories');
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $error = 'Kategori "' . $name . '" sudah ada.';
            }
        }

        Session::flash('error', $error);

        return redirect_route('admin.project-categories');
    }

    public function update(Request $request, int $id): Response
    {
        ProjectCategories::find($id) ?? abort(404);

        [$name, $error] = ProjectCategories::validateName($request->input('name', ''), $id);

        if ($error === null) {
            try {
                ProjectCategories::rename($id, $name);
                Session::flash('success', 'Kategori disimpan.');

                return redirect_route('admin.project-categories');
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $error = 'Kategori "' . $name . '" sudah ada.';
            }
        }

        Session::flash('error', $error);

        return redirect_route('admin.project-categories');
    }

    public function delete(int $id): Response
    {
        ProjectCategories::find($id) ?? abort(404);

        ProjectCategories::delete($id);
        Session::flash('success', 'Kategori dihapus. Proyek di dalamnya menjadi tanpa kategori.');

        return redirect_route('admin.project-categories');
    }
}