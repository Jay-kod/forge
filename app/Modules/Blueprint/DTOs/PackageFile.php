<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

readonly class PackageFile
{
    public function __construct(
        public string $path,
        public string $content,
        public string $description = ''
    ) {}
}
