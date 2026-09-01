<?php

declare(strict_types=1);

namespace App\Modules\Projects\Contracts;

use App\Modules\Projects\DTOs\ClassificationResult;

interface ClassificationServiceInterface
{
    public function classify(string $userInput): ClassificationResult;
}
