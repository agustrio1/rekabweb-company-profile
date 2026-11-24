<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Media;

final class MediaController
{
    private const MAX_FILES = 10;

    public function index(Request $request): Response
    {
        [$page, $pages, $rows] = $this->paginate($request);

        return view('admin/media', [
            'items' => array_map([$this, 'present'], $rows),
            'page' => $page,
            'pages' => $pages,
            'total' => Media::count(),
            'maxLabel' => Media::humanSize(Media::maxBytes()),
        ]);
    }

    /** JSON untuk pemilih gambar. */
    public function browse(Request $request): Response
    {
        [$page, $pages, $rows] = $this->paginate($request);

        return Response::json([
            'items' => array_map([$this, 'present'], $rows),
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function upload(Request $request): Response
    {
        $ajax = $request->header('X-Requested-With') === 'XMLHttpRequest';
        $userId = (int) (auth_user()['id'] ?? 0) ?: null;

        $uploaded = [];
        $errors = [];
        $files = $this->uploadedFiles();

        if ($files === []) {
            $errors[] = 'Pilih minimal satu gambar. Kalau sudah memilih, ukurannya mungkin melebihi batas server.';
        }

        foreach ($files as $file) {
            [$row, $error] = Media::store($file, $userId);
            if ($row !== null) {
                $uploaded[] = $this->present($row);
            } else {
                $errors[] = (string) $error;
            }
        }

        if ($ajax) {
            return Response::json(
                ['uploaded' => $uploaded, 'errors' => $errors],
                $uploaded === [] && $errors !== [] ? 422 : 200
            );
        }

        if ($uploaded !== []) {
            Session::flash('success', count($uploaded) . ' gambar diunggah.');
        }
        if ($errors !== []) {
            Session::flash('error', implode(' • ', $errors));
        }

        return redirect_route('admin.media');
    }

    public function update(Request $request, int $id): Response
    {
        Media::find($id) ?? abort(404);

        $alt = $request->input('alt', '');
        $alt = is_string($alt) ? trim((string) preg_replace('/\p{Cc}+/u', ' ', $alt)) : '';
        Media::updateAlt($id, mb_substr($alt, 0, 255));

        Session::flash('success', 'Alt text disimpan.');

        return $this->back($request);
    }

    public function delete(Request $request, int $id): Response
    {
        [$ok, $message] = Media::delete($id);
        Session::flash($ok ? 'success' : 'error', $message);

        return $this->back($request);
    }

    /** @return array{0: int, 1: int, 2: array} [halaman, jumlah halaman, baris] */
    private function paginate(Request $request): array
    {
        $pages = max(1, (int) ceil(Media::count() / Media::PER_PAGE));
        $page = min($pages, max(1, (int) $request->query('page', 1)));

        return [$page, $pages, Media::page($page)];
    }

    private function back(Request $request): Response
    {
        $page = (int) $request->input('page', 1);
        $url = route('admin.media') . ($page > 1 ? '?page=' . $page : '');

        return Response::redirect($url);
    }

    /** Ratakan struktur $_FILES['files'] menjadi daftar file. */
    private function uploadedFiles(): array
    {
        $f = $_FILES['files'] ?? null;
        if (!is_array($f) || !isset($f['name'])) {
            return [];
        }

        if (!is_array($f['name'])) {
            $list = [$f];
        } else {
            $list = [];
            foreach (array_keys($f['name']) as $i) {
                $list[] = [
                    'name' => $f['name'][$i] ?? '',
                    'tmp_name' => $f['tmp_name'][$i] ?? '',
                    'error' => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                ];
            }
        }

        $list = array_filter($list, static fn (array $x): bool => (int) ($x['error'] ?? 0) !== UPLOAD_ERR_NO_FILE);

        return array_slice(array_values($list), 0, self::MAX_FILES);
    }

    private function present(array $row): array
    {
        $id = (int) $row['id'];

        return [
            'id' => $id,
            'url' => Media::url((string) $row['path']),
            'name' => (string) $row['original_name'],
            'alt' => (string) $row['alt_text'],
            'mime' => (string) $row['mime'],
            'size' => Media::humanSize((int) $row['size']),
            'width' => (int) $row['width'],
            'height' => (int) $row['height'],
            'date' => date('d M Y H:i', (int) strtotime((string) $row['created_at'])),
            'update_url' => route('admin.media.update', ['id' => $id]),
            'delete_url' => route('admin.media.delete', ['id' => $id]),
        ];
    }
}