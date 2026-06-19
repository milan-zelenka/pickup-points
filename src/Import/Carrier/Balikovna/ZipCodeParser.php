<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use RuntimeException;

class ZipCodeParser
{
    /**
     * @throws RuntimeException
     */
    public function parse(string $address): string
    {
        // Two address formats: "street, district, zip, city" or "street, zip, city".
        // Zip is always the second-to-last comma-separated segment.
        $parts = explode(',', $address);
        $zip = trim($parts[count($parts) - 2] ?? '');

        if (!preg_match('/^\d{5}$/', $zip)) {
            throw new RuntimeException(sprintf('Cannot parse zip code from ADRESA: "%s"', $address));
        }

        return $zip;
    }
}
