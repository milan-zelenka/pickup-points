<?php

declare(strict_types=1);

namespace App\Import\Feed;

use App\PickupPoint\Carrier;
use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PickupPointFeedRegistryTest extends TestCase
{
    public function testReturnsFeedRegisteredForCarrier(): void
    {
        $feed = $this->createStub(PickupPointFeedReader::class);
        $feed->method('getCarrier')->willReturn(Carrier::BALIKOVNA);
        $registry = new PickupPointFeedRegistry([$feed]);

        $result = $registry->getFeed(Carrier::BALIKOVNA);

        Assert::assertSame($feed, $result);
    }

    public function testThrowsWhenNoFeedRegisteredForCarrier(): void
    {
        $registry = new PickupPointFeedRegistry([]);

        $this->expectExceptionObject(
            new LogicException('No feed registered for carrier "balikovna".'),
        );

        $registry->getFeed(Carrier::BALIKOVNA);
    }
}
