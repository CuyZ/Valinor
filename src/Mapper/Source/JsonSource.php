<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidJson;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Exception\SourceNotIterable;
use Iterator;
use IteratorAggregate;
use JsonException;
use Traversable;

use function is_iterable;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class JsonSource implements IteratorAggregate
{
    /** @var iterable<mixed> */
    private iterable $source;

    /**
     * @throws InvalidSource
     */
    public function __construct(string $jsonSource)
    {
        try {
            $source = json_decode($jsonSource, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidJson($jsonSource, $e);
        }

        if (! is_iterable($source)) {
            throw new SourceNotIterable($jsonSource);
        }

        $this->source = $source;
    }

    /**
     * @return Iterator<mixed>
     */
    public function getIterator(): Traversable
    {
        yield from $this->source;
    }
}
