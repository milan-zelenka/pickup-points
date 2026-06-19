<?php

declare(strict_types=1);

namespace App\Import\Feed;

use App\PickupPoint\Carrier;

interface FeedRegistry
{
    public function getFeed(Carrier $carrier): PickupPointFeedReader;
}
