<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Auth;

final class AuthController
{
    public function showLogin(): Response
    {
        return view('admin/auth/login');
    }

    public function login(Request $request): Response
    {
        $email = $this->text($request, 'email', 190);
        $password = $this->text($request, 'password', 200, false);

        if ($email === '' || $password === '') {
            return $this->fail($email, 'Email dan password wajib diisi.');
        }

        if (Auth::isThrottled($email, $request->ip())) {
            return $this->fail($email, 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.');
        }

        $user = Auth::attempt($email, $password, $request->ip());

        if ($user === null) {
            return $this->fail($email, 'Email atau password salah.');
        }

        Auth::login($user);

        $intended = Session::pull('intended');
        $valid = is_string($intended)
            && preg_match('#^/admin(/[A-Za-z0-9_\-/.]*)?$#', $intended) === 1
            && $intended !== '/admin/login';

        return $valid
            ? Response::redirect(url($intended))
            : redirect_route('admin.dashboard');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();

        // Mulai session baru supaya pesan flash bisa ditampilkan di halaman login
        Session::start($request);
        Session::flash('success', 'Kamu sudah keluar.');

        return redirect_route('admin.login');
    }

    private function fail(string $email, string $message): Response
    {
        Session::flash('error', $message);
        Session::flash('old_email', $email);

        return redirect_route('admin.login');
    }

    /** Ambil input sebagai string (menolak array) dan batasi panjangnya. */
    private function text(Request $request, string $key, int $max, bool $trim = true): string
    {
        $value = $request->input($key, '');

        if (!is_string($value) || strlen($value) > $max) {
            return '';
        }

        $value = $trim ? trim($value) : $value;

        return $key === 'email' ? strtolower($value) : $value;
    }
}