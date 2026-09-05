<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

readonly class DevPackage
{
    /**
     * @param array<int, PackageFile> $files
     */
    public function __construct(
        public string $projectName,
        public string $version,
        public array $files,
        public string $masterPrompt
    ) {}

    /**
     * @return array<string, string>
     */
    public function toFileMap(): array
    {
        $map = [];
        foreach ($this->files as $file) {
            $map[$file->path] = $file->content;
        }
        return $map;
    }
}
