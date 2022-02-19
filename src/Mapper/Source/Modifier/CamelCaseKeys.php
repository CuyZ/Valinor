<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use IteratorAggregate;
use Traversable;

use function is_iterable;

/**
 * @api
 * @implements IteratorAggregate<string, mixed>
 */
final class CamelCaseKeys implements IteratorAggregate
{
    /** @var array<string, mixed> */
    private array $source;

    /**
     * @param iterable<string, mixed> $source
     */
    public function __construct(iterable $source)
    {
        $this->source = $this->replace($source);
    }

    /**
     * @param iterable<string, mixed> $source
     * @return array<string, mixed>
     */
    private function replace(iterable $source): array
    {
        $result = [];

        foreach ($source as $key => $value) {
            if (is_iterable($value)) {
                $value = $this->replace($value);
            }

            $camelCaseKey = $this->camelCaseKeys($key);

            if (isset($result[$camelCaseKey])) {
                continue;
            }

            $result[$camelCaseKey] = $value;
        }

        return $result;
    }

    private function camelCaseKeys(string $key): string
    {
        return lcfirst(str_replace([' ', '_', '-'], '', ucwords($key, ' _-')));
    }

    public function getIterator(): Traversable
    {
        yield from $this->source;
    }
}
