<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function __construct(
        private string $body = '',
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new self($body, $status, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            header_remove('X-Powered-By');

            // Header dasar; CSP dan lainnya ditambah di PHASE 9
            $defaults = [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Referrer-Policy' => 'strict-origin-when-cross-origin',
            ];

            foreach ($this->headers + $defaults as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $this->body;
    }
}