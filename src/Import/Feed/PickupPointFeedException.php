<?php

declare(strict_types=1);

namespace App\Import\Feed;

use RuntimeException;

class PickupPointFeedException extends RuntimeException
{
    public static function cannotReadFeed(string $feedUrl, \Throwable $previous): self
    {
        return new self(
            sprintf('Cannot read pickup point feed "%s": %s', $feedUrl, $previous->getMessage()),
            previous: $previous,
        );
    }
}
