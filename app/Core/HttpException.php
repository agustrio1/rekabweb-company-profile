<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

final class HttpException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message = '',
        public readonly array $headers = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}