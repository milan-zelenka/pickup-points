<?php

declare(strict_types=1);

namespace App\Import\Feed;

use App\PickupPoint\Carrier;
use LogicException;

readonly class PickupPointFeedRegistry implements FeedRegistry
{
    /** @param iterable<PickupPointFeedReader> $feeds */
    public function __construct(private iterable $feeds)
    {
    }

    public function getFeed(Carrier $carrier): PickupPointFeedReader
    {
        $feedsByCarrier = [];
        foreach ($this->feeds as $feed) {
            $feedsByCarrier[$feed->getCarrier()->value] = $feed;
        }

        // Carrier is in the enum but has no registered feed.
        return $feedsByCarrier[$carrier->value]
            ?? throw new LogicException(sprintf('No feed registered for carrier "%s".', $carrier->value));
    }
}
