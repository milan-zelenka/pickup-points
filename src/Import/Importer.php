<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\ImportProgressReporter\ImportProgressReporter;
use App\PickupPoint\Carrier;

interface Importer
{
    public function import(Carrier $carrier, ImportProgressReporter $progress): void;
}
