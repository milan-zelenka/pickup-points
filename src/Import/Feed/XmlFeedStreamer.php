<?php

declare(strict_types=1);

namespace App\Import\Feed;

use Generator;
use RuntimeException;
use SimpleXMLElement;

interface XmlFeedStreamer
{
    /**
     * @return Generator<SimpleXMLElement>
     * @throws RuntimeException
     */
    public function streamNodes(string $feedUrl, string $nodeName): Generator;

    /** @throws RuntimeException */
    public function countNodes(string $feedUrl, string $nodeName): int;
}
