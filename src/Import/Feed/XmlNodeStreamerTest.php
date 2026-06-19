<?php

declare(strict_types=1);

namespace App\Import\Feed;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

class XmlNodeStreamerTest extends TestCase
{
    private const string FIXTURE = __DIR__ . '/__fixtures__/points.xml';

    public function testCountNodesReturnsCorrectCount(): void
    {
        $streamer = new XmlNodeStreamer();

        $result = $streamer->countNodes(self::FIXTURE, 'point');

        Assert::assertSame(3, $result);
    }

    public function testCountNodesReturnsZeroForAbsentElementName(): void
    {
        $streamer = new XmlNodeStreamer();

        $result = $streamer->countNodes(self::FIXTURE, 'nonexistent');

        Assert::assertSame(0, $result);
    }

    public function testStreamNodesYieldsMatchingCount(): void
    {
        $streamer = new XmlNodeStreamer();

        $items = iterator_to_array($streamer->streamNodes(self::FIXTURE, 'point'), false);

        Assert::assertCount(3, $items);
    }

    public function testStreamNodesYieldsSimpleXmlElements(): void
    {
        $streamer = new XmlNodeStreamer();

        $items = iterator_to_array($streamer->streamNodes(self::FIXTURE, 'point'), false);

        foreach ($items as $element) {
            Assert::assertInstanceOf(SimpleXMLElement::class, $element);
            Assert::assertNotEmpty((string) $element->name);
        }
    }

    public function testStreamNodesDoesNotYieldNonMatchingNodes(): void
    {
        $streamer = new XmlNodeStreamer();

        $items = iterator_to_array($streamer->streamNodes(self::FIXTURE, 'meta'), false);

        Assert::assertCount(1, $items);
        Assert::assertSame('ignored', (string) $items[0]);
    }

    public function testCountNodesThrowsOnInvalidFeedUrl(): void
    {
        $streamer = new XmlNodeStreamer();

        $this->expectException(RuntimeException::class);

        $streamer->countNodes('/nonexistent/path.xml', 'point');
    }

    public function testStreamNodesThrowsOnInvalidFeedUrl(): void
    {
        $streamer = new XmlNodeStreamer();

        $this->expectException(RuntimeException::class);

        iterator_to_array($streamer->streamNodes('/nonexistent/path.xml', 'point'));
    }
}
