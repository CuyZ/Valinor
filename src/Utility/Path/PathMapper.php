<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Path;

/** @internal */
final class PathMapper
{
    /**
     * @param iterable<mixed> $source
     * @param array<string, string> $map
     * @return array<mixed>
     */
    public function map(iterable $source, array $map): array
    {
        return $this->doMap($source, $this->prepareMappings($map));
    }

    /**
     * @param iterable<mixed> $source
     * @param array<Mapping> $mappings
     * @return array<mixed>
     */
    private function doMap(iterable $source, array $mappings, int $depth = 0): array
    {
        $out = [];

        foreach ($source as $key => $value) {
            /** @var int|string $key */
            $newMappings = array_filter($mappings, static fn (Mapping $mapping) => $mapping->matches($key, $depth));

            $newKey = $this->findMapping($newMappings, $depth, $key);

            if (is_array($value)) {
                $out[$newKey] = $this->doMap($value, $newMappings, $depth + 1);

                continue;
            }

            $out[$newKey] = $value;
        }

        return $out;
    }

    /**
     * @param array<string, string> $map
     * @return array<Mapping>
     */
    private function prepareMappings(array $map): array
    {
        $mappings = [];

        foreach ($map as $from => $to) {
            $mappings[] = new Mapping(explode('.', $from), $to);
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
