<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Settings;

final class SettingsController
{
    public function index(): Response
    {
        $old = Session::pullFlash('old');
        $errors = Session::pullFlash('errors');
        $old = is_array($old) ? $old : [];
        $errors = is_array($errors) ? $errors : [];

        $groups = Settings::groups();

        // Buka tab pertama yang punya error, kalau tidak ada tab pertama
        $initial = (string) array_key_first($groups);
        foreach ($groups as $groupKey => $group) {
            if (array_intersect_key($errors, $group['fields']) !== []) {
                $initial = (string) $groupKey;
                break;
            }
        }

        return view('admin/settings', [
            'groups' => $groups,
            'values' => $old + Settings::all(),
            'errors' => $errors,
            'initial' => $initial,
        ]);
    }

    public function update(Request $request): Response
    {
        $input = [];
        foreach (array_keys(Settings::fields()) as $key) {
            $input[$key] = $request->input($key, '');
        }

        [$values, $errors] = Settings::validate($input);

        if ($errors !== []) {
            Session::flash('error', 'Periksa kembali isian yang ditandai.');
            Session::flash('errors', $errors);
            Session::flash('old', $values);

            return redirect_route('admin.settings');
        }

        Settings::save($values);
        Session::flash('success', 'Pengaturan disimpan.');

        return redirect_route('admin.settings');
    }
}