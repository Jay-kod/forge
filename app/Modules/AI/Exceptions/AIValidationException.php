<?php

declare(strict_types=1);

namespace App\Modules\AI\Exceptions;

use RuntimeException;

class AIValidationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly array $errors = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
