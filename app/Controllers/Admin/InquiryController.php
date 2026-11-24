<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Inquiries;

final class InquiryController
{
    public function index(Request $request): Response
    {
        $q = $request->query('q', '');
        $q = is_string($q) ? mb_substr(trim($q), 0, 100) : '';
        $status = $request->query('status', '');
        $status = is_string($status) && isset(Inquiries::statuses()[$status]) ? $status : '';

        $total = Inquiries::count($q, $status);
        $pages = max(1, (int) ceil($total / Inquiries::PER_PAGE));
        $page = min($pages, max(1, (int) $request->query('page', 1)));

        $rows = Inquiries::search($q, $status, $page);
        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $row['show_url'] = route('admin.inquiries.show', ['id' => $id]);
            $row['delete_url'] = route('admin.inquiries.delete', ['id' => $id]);
        }
        unset($row);

        return view('admin/inquiries/index', [
            'rows' => $rows,
            'q' => $q,
            'status' => $status,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'counts' => Inquiries::statusCounts(),
            'statuses' => Inquiries::statuses(),
        ]);
    }

    public function show(int $id): Response
    {
        $inquiry = Inquiries::find($id) ?? abort(404);

        $values = $this->flashedOld() + [
            'status' => (string) $inquiry['status'],
            'admin_note' => (string) ($inquiry['admin_note'] ?? ''),
        ];

        return view('admin/inquiries/show', [
            'inquiry' => $inquiry,
            'values' => $values,
            'errors' => $this->flashedErrors(),
            'statuses' => Inquiries::statuses(),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        Inquiries::find($id) ?? abort(404);

        $input = [
            'status' => is_string($request->input('status')) ? (string) $request->input('status') : '',
            'admin_note' => is_string($request->input('admin_note')) ? (string) $request->input('admin_note') : '',
        ];

        [$values, $errors] = Inquiries::validateUpdate($input);

        if ($errors === []) {
            Inquiries::update($id, $values, (int) (auth_user()['id'] ?? 0) ?: null);
            Session::flash('success', 'Inquiry disimpan.');

            return redirect_route('admin.inquiries.show', ['id' => $id]);
        }

        Session::flash('error', 'Periksa kembali isian yang ditandai.');
        Session::flash('errors', $errors);
        Session::flash('old', $input);

        return redirect_route('admin.inquiries.show', ['id' => $id]);
    }

    public function delete(int $id): Response
    {
        Inquiries::find($id) ?? abort(404);

        Inquiries::delete($id);
        Session::flash('success', 'Inquiry dihapus.');

        return redirect_route('admin.inquiries');
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