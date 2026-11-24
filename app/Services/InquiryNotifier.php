<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\ErrorHandler;
use RuntimeException;
use Throwable;

/** Email pemberitahuan inquiry baru ke pemilik. */
final class InquiryNotifier
{
    /**
     * Jadwalkan pengiriman setelah respons ke pengunjung selesai dikirim.
     *
     * @param array<string, string> $v nilai inquiry yang sudah divalidasi
     */
    public static function queue(array $v, int $id): void
    {
        if (!Mailer::enabled()) {
            return;
        }

        register_shutdown_function(static function () use ($v, $id): void {
            // Lepaskan koneksi pengunjung dulu supaya halaman terima kasih tidak menunggu SMTP
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } elseif (function_exists('litespeed_finish_request')) {
                litespeed_finish_request();
            }

            self::send($v, $id);
        });
    }

    /** @param array<string, string> $v */
    public static function send(array $v, int $id): void
    {
        try {
            $to = Mailer::recipients();
            if ($to === []) {
                return;
            }

            $site = Site::name();
            $digits = (string) preg_replace('/\D+/', '', (string) ($v['whatsapp'] ?? ''));
            $origin = trim((string) config('app.url', ''));

            $lines = [
                'Ada inquiry baru di website ' . $site . '.',
                '',
                'Nama       : ' . $v['name'],
                'Email      : ' . $v['email'],
                'WhatsApp   : ' . ($digits !== '' ? '+' . $digits : '-'),
                'Perusahaan : ' . (($v['company'] ?? '') !== '' ? $v['company'] : '-'),
                'Kebutuhan  : ' . (($v['need_type'] ?? '') !== '' ? $v['need_type'] : '-'),
                'Waktu      : ' . date('d-m-Y H:i') . ' (' . date_default_timezone_get() . ')',
                '',
                'Pesan:',
                (string) $v['message'],
                '',
                '----',
            ];
            if ($origin !== '') {
                $lines[] = 'Buka di admin : ' . Seo::absolute(url('/admin/inquiries/' . $id));
            }
            if ($digits !== '') {
                $lines[] = 'Balas WhatsApp: https://wa.me/' . $digits;
            }
            $lines[] = 'Balas email   : cukup balas email ini.';

            [$ok, $error] = Mailer::send(
                $to,
                'Inquiry baru dari ' . $v['name'],
                implode("\n", $lines) . "\n",
                ['email' => (string) $v['email'], 'name' => (string) $v['name']]
            );

            if (!$ok) {
                ErrorHandler::log(new RuntimeException('Email inquiry #' . $id . ' gagal dikirim: ' . $error));
            }
        } catch (Throwable $e) {
            ErrorHandler::log($e);
        }
    }
}