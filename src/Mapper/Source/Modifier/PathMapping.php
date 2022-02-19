<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use IteratorAggregate;
use Traversable;

use function is_array;
use function is_numeric;
use function str_ends_with;
use function strlen;

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
        $this->source = $this->map($source, $map);
    }

    public function getIterator(): Traversable
    {
        yield from $this->source;
    }

    /**
     * @param iterable<mixed> $source
     * @param array<string, string> $map
     * @return array<mixed>
     */
    private function map(iterable $source, array $map, string $currentPath = ''): array
    {
        $out = [];

        foreach ($source as $key => $value) {
            /** @var int|string $key */
            $currentPath = $this->addToPath($currentPath, $key);

            if (is_array($value)) {
                $out[$map[$currentPath] ?? $key] = $this->map($value, $map, $currentPath);

                continue;
            }

            $out[$map[$currentPath] ?? $key] = $value;
        }

        return $out;
    }

    /**
     * @param int|string $keyToAdd
     */
    private function addToPath(string $path, $keyToAdd): string
    {
        if (is_numeric($keyToAdd)) {
            $keyToAdd = '*';
        }

        if (strlen($path) === 0) {
            return $keyToAdd;
        }

        if ($keyToAdd === '*' && str_ends_with($path, '*')) {
            return $path;
        }

        return "$path.$keyToAdd";
    }
}
