<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use CuyZ\Valinor\Utility\Path\PathMapper;
use IteratorAggregate;
use Traversable;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class PathMapping implements IteratorAggregate
{
    /** @var array<string, mixed> */
    private array $source;

    /**
     * @param iterable<mixed> $source
     * @param array<string, string> $map
     */
    public function __construct(iterable $source, array $map)
    {
        $this->source = (new PathMapper())->map($source, $map);
    }

    public function getIterator(): Traversable
    {
        yield from $this->source;
    }
}
