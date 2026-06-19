<?php

declare(strict_types=1);

namespace App\Import\Feed;

use Exception;
use Generator;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;

class XmlNodeStreamer implements XmlFeedStreamer
{
    /**
     * @return Generator<SimpleXMLElement>
     * @throws RuntimeException
     */
    public function streamNodes(string $feedUrl, string $nodeName): Generator
    {
        $reader = $this->openReader($feedUrl);

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === $nodeName) {
                    try {
                        yield new SimpleXMLElement($reader->readOuterXML());
                    } catch (Exception $exception) {
                        throw new RuntimeException('Cannot parse XML element: ' . $exception->getMessage(), previous: $exception);
                    }
                }
            }
        } finally {
            $reader->close();
        }
    }

    /**
     * @throws RuntimeException
     */
    public function countNodes(string $feedUrl, string $nodeName): int
    {
        $reader = $this->openReader($feedUrl);

        $count = 0;
        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === $nodeName) {
                    ++$count;
                }
            }
        } finally {
            $reader->close();
        }

        return $count;
    }

    /** @throws RuntimeException */
    private function openReader(string $feedUrl): XMLReader
    {
        $reader = new XMLReader();

        // XMLReader::open() emits a warning and returns false on failure — fail fast instead.
        if (@$reader->open($feedUrl) === false) {
            throw new RuntimeException(sprintf('Cannot open XML feed: %s', $feedUrl));
        }

        return $reader;
    }
}
