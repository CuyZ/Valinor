<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use IteratorAggregate;
use Traversable;

use function array_filter;
use function explode;
use function is_array;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class PathMapping implements IteratorAggregate
{
    /** @var array<mixed> */
    private array $source;

    /**
     * @param iterable<mixed> $source
     * @param array<string> $map
     */
    public function __construct(iterable $source, array $map)
    {
        $this->source = $this->map($source, $this->prepareMappings($map));
    }

    public function getIterator(): Traversable
    {
        yield from $this->source;
    }

    /**
     * @param iterable<mixed> $source
     * @param array<Mapping> $mappings
     * @return array<mixed>
     */
    private function map(iterable $source, array $mappings, int $depth = 0): array
    {
        $out = [];

        foreach ($source as $key => $value) {
            /** @var int|string $key */
            $newMappings = array_filter($mappings, fn (Mapping $mapping) => $mapping->matches($key, $depth));

            $newKey = $this->findMapping($newMappings, $depth, $key);

            if (is_array($value)) {
                $out[$newKey] = $this->map($value, $newMappings, $depth + 1);

                continue;
            }

            $out[$newKey] = $value;
        }

        return $out;
    }

    /**
     * @param array<string> $map
     * @return array<Mapping>
     */
    private function prepareMappings(array $map): array
    {
        $mappings = [];

        foreach ($map as $from => $to) {
            $mappings[] = new Mapping(explode('.', (string)$from), $to);
        }

        return $mappings;
    }

    /**
     * @param array<Mapping> $mappings
     */
    private function findMapping(array $mappings, int $atDepth, int|string $key): int|string
    {
        foreach ($mappings as $mapping) {
            $mappedKey = $mapping->findMappedKey($key, $atDepth);

            if (null !== $mappedKey) {
                return $mappedKey;
            }
        }

        return $key;
    }
}
