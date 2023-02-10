<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidJson;
use CuyZ\Valinor\Mapper\Source\Exception\SourceNotIterable;
use Iterator;
use IteratorAggregate;
use Traversable;

use function is_iterable;
use function json_decode;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class JsonSource implements IteratorAggregate
{
    /** @var iterable<mixed> */
    private iterable $source;

    public function __construct(string $jsonSource)
    {
        $source = json_decode($jsonSource, true);

        if ($source === null) {
            throw new InvalidJson();
        }

        if (! is_iterable($source)) {
            throw new SourceNotIterable($source);
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
