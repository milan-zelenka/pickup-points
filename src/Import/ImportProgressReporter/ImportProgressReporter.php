<?php

declare(strict_types=1);

namespace App\Import\ImportProgressReporter;

interface ImportProgressReporter
{
    public function preparing(): void;

    public function start(int $total): void;

    public function setProgress(int $imported): void;

    public function finish(): void;
}
