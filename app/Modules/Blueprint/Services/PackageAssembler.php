<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Services;

use App\Modules\Blueprint\DTOs\DevPackage;
use RuntimeException;
use ZipArchive;

class PackageAssembler
{
    /**
     * Assemble development package into a temporary ZIP file.
     * Returns the absolute path to the temporary ZIP file.
     */
    public function assemble(DevPackage $package): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'forge_pkg_');
        if (!$tempPath) {
            throw new RuntimeException('Unable to allocate temporary file for package assembly.');
        }

        $zip = new ZipArchive();
        $res = $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res !== true) {
            throw new RuntimeException("Failed to create ZIP archive: code {$res}");
        }

        foreach ($package->files as $file) {
            $zip->addFromString($file->path, $file->content);
        }

        $zip->close();

        return $tempPath;
    }
}
