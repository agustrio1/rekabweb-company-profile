<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Response;
use App\Services\FormGuard;
use App\Services\InquiryNotifier;
use App\Services\Inquiries;
use App\Services\PublicPages;
use Throwable;

final class ContactController
{
    private const FIELDS = ['name', 'email', 'whatsapp', 'company', 'need_type', 'message'];

    public function show(): Response
    {
        return $this->render([], [], 200);
    }

    public function thanks(): Response
    {
        return view('site/contact/thanks');
    }

    public function submit(Request $request): Response
    {
        // Kiriman dari situs lain ditolak
        if (!FormGuard::sameOrigin($request)) {
            abort(403);
        }

        // Honeypot: bot mengisinya. Pura-pura sukses, tidak disimpan.
        $trap = $request->input(FormGuard::HONEYPOT, '');
        if (!is_string($trap) || $trap !== '') {
            return redirect_route('contact.thanks');
        }

        $input = [];
        foreach (self::FIELDS as $key) {
            $value = $request->input($key, '');
            $input[$key] = is_string($value) ? $value : '';
        }

        $state = FormGuard::check($request->input(FormGuard::FIELD, ''));
        if ($state !== 'ok') {
            $message = match ($state) {
                'fast' => 'Formulir terkirim terlalu cepat. Tunggu beberapa detik lalu kirim lagi.',
                'expired' => 'Formulir sudah kedaluwarsa. Isian kamu aman di bawah, silakan kirim lagi.',
                default => 'Formulir tidak valid. Muat ulang halaman lalu coba lagi.',
            };

            return $this->render($input, ['_form' => $message], 422);
        }

        if (Inquiries::throttled($request->ip())) {
            return $this->render($input, [
                '_form' => 'Terlalu banyak pesan dari jaringan ini. Coba lagi dalam satu jam atau hubungi kami lewat WhatsApp.',
            ], 429);
        }

        [$values, $errors] = Inquiries::validatePublic($input);

        if ($values['need_type'] !== '' && !in_array($values['need_type'], PublicPages::needTypes(), true)) {
            $values['need_type'] = '';
            $errors['need_type'] = 'Pilihan jenis kebutuhan tidak valid.';
        }

        if ($errors !== []) {
            return $this->render($input, $errors, 422);
        }

        try {
            $id = Inquiries::create($values, $request->ip());
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return $this->render($input, [
                '_form' => 'Pesan belum bisa dikirim karena gangguan di server. Silakan coba lagi sebentar lagi.',
            ], 500);
        }

        // Email dikirim setelah halaman terima kasih tampil; kegagalannya tidak memengaruhi inquiry
        InquiryNotifier::queue($values, $id);

        return redirect_route('contact.thanks');
    }

    private function render(array $values, array $errors, int $status): Response
    {
        $v = [];
        foreach (self::FIELDS as $key) {
            $v[$key] = is_string($values[$key] ?? null) ? (string) $values[$key] : '';
        }

        return view('site/contact/index', [
            'v' => $v,
            'e' => $errors,
            'needTypes' => PublicPages::needTypes(),
            'token' => FormGuard::token(),
        ], $status)->header('Cache-Control', 'no-store');
    }
}