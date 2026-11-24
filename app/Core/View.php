<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

final class View
{
    private ?string $layout = null;
    private array $layoutData = [];

    private function __construct()
    {
    }

    public static function render(string $name, array $data = []): string
    {
        return (new self())->renderView($name, $data);
    }

    /** Dipanggil dari dalam file view: $this->layout('site/layout', ['title' => 'Beranda']); */
    public function layout(string $name, array $data = []): void
    {
        $this->layout = $name;
        $this->layoutData = $data;
    }

    /** Dipanggil dari dalam view: $this->partial('site/partials/header'); */
    public function partial(string $name, array $data = []): void
    {
        echo self::render($name, $data);
    }

    private function renderView(string $name, array $data): string
    {
        $content = $this->capture($name, $data);

        if ($this->layout !== null) {
            $layout = $this->layout;
            $layoutData = $this->layoutData;
            $this->layout = null;

            return $this->capture($layout, array_merge($data, $layoutData, ['content' => $content]));
        }

        return $content;
    }

    private function capture(string $__view, array $__data): string
    {
        if (!preg_match('#^[A-Za-z0-9_\-/]+$#', $__view)) {
            throw new RuntimeException("Nama view tidak valid: $__view");
        }

        $__file = BASE_PATH . '/app/Views/' . $__view . '.php';
        if (!is_file($__file)) {
            throw new RuntimeException("View tidak ditemukan: $__view");
        }

        extract($__data, EXTR_SKIP);
        ob_start();

        try {
            include $__file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}