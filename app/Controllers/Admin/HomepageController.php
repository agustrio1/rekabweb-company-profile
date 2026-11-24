<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Homepage;

final class HomepageController
{
    public function index(): Response
    {
        $old = Session::pullFlash('old');
        $errors = Session::pullFlash('errors');

        $sections = Homepage::sections();
        if (is_array($old) && $old !== []) {
            $sections = Homepage::overlay($sections, $old);
        }

        return view('admin/homepage', [
            'sections' => $sections,
            'defs' => Homepage::definitions(),
            'errors' => is_array($errors) ? $errors : [],
        ]);
    }

    public function update(Request $request): Response
    {
        $s = $request->input('s', []);
        $s = is_array($s) ? $s : [];
        $order = $request->input('order', []);
        $active = $request->input('active', []);

        [$rows, $errors] = Homepage::validate($s, $order, $active);

        if ($errors !== []) {
            Session::flash('error', 'Periksa kembali isian yang ditandai.');
            Session::flash('errors', $errors);
            Session::flash('old', ['s' => $s, 'order' => $order, 'active' => $active]);

            return redirect_route('admin.homepage');
        }

        Homepage::save($rows);
        Session::flash('success', 'Beranda disimpan.');

        return redirect_route('admin.homepage');
    }
}