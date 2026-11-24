<?php

declare(strict_types=1);

namespace App\Services;

/** Data situs untuk layout publik (nama, navigasi, kontak, social media). */
final class Site
{
    /** Menu utama. Path diisi halaman yang dibuat di PHASE 8C sampai 8E. */
    public static function nav(): array
    {
        return [
            ['key' => 'home', 'label' => 'Beranda', 'path' => '/'],
            ['key' => 'about', 'label' => 'Tentang', 'path' => '/tentang'],
            ['key' => 'services', 'label' => 'Layanan', 'path' => '/layanan'],
            ['key' => 'projects', 'label' => 'Proyek', 'path' => '/proyek'],
            ['key' => 'blog', 'label' => 'Blog', 'path' => '/blog'],
            ['key' => 'contact', 'label' => 'Kontak', 'path' => '/kontak'],
        ];
    }

    public static function name(): string
    {
        $name = trim((string) setting('company_name', ''));

        return $name !== '' ? $name : (string) config('app.name');
    }

    /** Path internal diubah jadi URL aplikasi, URL lengkap dibiarkan. */
    public static function link(string $target): string
    {
        $target = trim($target);

        if ($target === '') {
            return url('/');
        }

        return str_starts_with($target, '/') ? url($target) : $target;
    }

    /** @return array{label: string, url: string} */
    public static function cta(): array
    {
        $label = trim((string) setting('cta_label', ''));

        return [
            'label' => $label !== '' ? $label : 'Hubungi kami',
            'url' => self::link((string) setting('cta_url', '/kontak')),
        ];
    }

    /** Hanya angka, format 62…; kosong kalau belum diisi. */
    public static function whatsappDigits(): string
    {
        return (string) preg_replace('/\D+/', '', (string) setting('whatsapp', ''));
    }

    public static function whatsappLink(string $text = ''): string
    {
        $digits = self::whatsappDigits();
        if ($digits === '') {
            return '';
        }

        return 'https://wa.me/' . $digits . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }

    /** Link tel: dari nomor telepon, atau '' kalau kosong. */
    public static function telHref(): string
    {
        $tel = (string) preg_replace('/[^0-9+]/', '', (string) setting('phone', ''));

        return $tel !== '' ? 'tel:' . $tel : '';
    }

    /** @return array<int, array{label: string, url: string}> hanya yang sudah diisi */
    public static function socials(): array
    {
        $map = [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
        ];

        $out = [];
        foreach ($map as $key => $label) {
            $value = trim((string) setting($key, ''));
            if ($value !== '') {
                $out[] = ['label' => $label, 'url' => $value];
            }
        }

        return $out;
    }
}