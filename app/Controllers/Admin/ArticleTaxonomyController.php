<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ArticleTaxonomy;
use PDOException;

/** Kategori dan tag artikel. {type} = categories atau tags. */
final class ArticleTaxonomyController
{
    public function index(string $type): Response
    {
        $this->guard($type);

        return view('admin/articles/taxonomy', [
            'type' => $type,
            'meta' => ArticleTaxonomy::meta($type),
            'rows' => ArticleTaxonomy::all($type),
            'canDelete' => can('content.delete_any'),
        ]);
    }

    public function store(Request $request, string $type): Response
    {
        $this->guard($type);
        $meta = ArticleTaxonomy::meta($type);

        if (ArticleTaxonomy::count($type) >= $meta['max']) {
            Session::flash('error', 'Maksimal ' . $meta['max'] . ' ' . $meta['noun'] . '.');

            return $this->back($type);
        }

        [$name, $error] = ArticleTaxonomy::validateName($type, $request->input('name', ''), null);

        if ($error === null) {
            try {
                ArticleTaxonomy::create($type, $name);
                Session::flash('success', $meta['singular'] . ' "' . $name . '" dibuat.');

                return $this->back($type);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $error = $meta['singular'] . ' "' . $name . '" sudah ada.';
            }
        }

        Session::flash('error', $error);

        return $this->back($type);
    }

    public function update(Request $request, string $type, int $id): Response
    {
        $this->guard($type);
        ArticleTaxonomy::find($type, $id) ?? abort(404);
        $meta = ArticleTaxonomy::meta($type);

        [$name, $error] = ArticleTaxonomy::validateName($type, $request->input('name', ''), $id);

        if ($error === null) {
            try {
                ArticleTaxonomy::rename($type, $id, $name);
                Session::flash('success', $meta['singular'] . ' disimpan.');

                return $this->back($type);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                $error = $meta['singular'] . ' "' . $name . '" sudah ada.';
            }
        }

        Session::flash('error', $error);

        return $this->back($type);
    }

    public function delete(string $type, int $id): Response
    {
        $this->guard($type);
        ArticleTaxonomy::find($type, $id) ?? abort(404);
        $meta = ArticleTaxonomy::meta($type);

        ArticleTaxonomy::delete($type, $id);
        Session::flash('success', $meta['singular'] . ' dihapus.');

        return $this->back($type);
    }

    private function guard(string $type): void
    {
        if (!ArticleTaxonomy::isType($type)) {
            abort(404);
        }
    }

    private function back(string $type): Response
    {
        return redirect_route('admin.taxonomy', ['type' => $type]);
    }
}