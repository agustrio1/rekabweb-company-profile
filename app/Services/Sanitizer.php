<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use RuntimeException;

/** Membersihkan HTML dari editor. Dipanggil sebelum konten disimpan ke database. */
final class Sanitizer
{
    private static ?HTMLPurifier $purifier = null;

    /** Hasil bersih, atau '' kalau tidak ada isi yang berarti. */
    public static function clean(string $html): string
    {
        $html = trim($html);

        if ($html === '' || !mb_check_encoding($html, 'UTF-8')) {
            return '';
        }

        $clean = trim(self::purifier()->purify($html));

        // Hanya paragraf kosong: anggap kosong
        if (trim(strip_tags($clean)) === '' && preg_match('/<(img|table|hr)\b/i', $clean) !== 1) {
            return '';
        }

        return $clean;
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        if (!class_exists(HTMLPurifier::class)) {
            throw new RuntimeException(
                "HTMLPurifier belum terpasang. Jalankan 'composer require ezyang/htmlpurifier' "
                . 'dan pastikan folder vendor/ ikut ter-upload.'
            );
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');

        // Whitelist tag dan atribut. Judul 1 tidak diizinkan (dipakai judul halaman).
        $config->set('HTML.Allowed', implode(',', [
            'p[style]', 'h2[style]', 'h3[style]', 'h4[style]',
            'blockquote', 'strong', 'b', 'em', 'i', 'u', 's', 'br', 'hr',
            'ul', 'ol', 'li',
            'a[href|title|target|rel]',
            'img[src|alt|title|width|height]',
            'table', 'thead', 'tbody', 'tr', 'th[colspan|rowspan]', 'td[colspan|rowspan]',
        ]));

        $config->set('CSS.AllowedProperties', ['text-align']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.AllowedRel', ['noopener', 'noreferrer', 'nofollow']);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('Attr.DefaultImageAlt', '');

        // Gambar dari domain lain dibuang; hanya path relatif atau domain sendiri
        $host = parse_url((string) config('app.url', ''), PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            $config->set('URI.Host', $host);
            $config->set('URI.DisableExternalResources', true);
        }

        $cache = BASE_PATH . '/storage/cache/htmlpurifier';
        if (!is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }
        if (is_dir($cache) && is_writable($cache)) {
            $config->set('Cache.SerializerPath', $cache);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        return self::$purifier = new HTMLPurifier($config);
    }
}