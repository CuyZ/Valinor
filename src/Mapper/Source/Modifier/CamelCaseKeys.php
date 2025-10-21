<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use IteratorAggregate;
use Traversable;

use function assert;
use function is_int;
use function is_iterable;
use function is_string;
use function lcfirst;
use function str_replace;
use function ucwords;

/**
 * @api
 * @implements IteratorAggregate<mixed>
 */
final class CamelCaseKeys implements IteratorAggregate
{
    /** @var array<mixed> */
    private array $source;

    /**
     * @param iterable<mixed> $source
     */
    public function __construct(iterable $source)
    {
        $this->source = $this->replace($source);
    }

    /**
     * @param iterable<mixed> $source
     * @return array<mixed>
     */
    private function replace(iterable $source): array
    {
        $result = [];

        foreach ($source as $key => $value) {
            assert(is_string($key) || is_int($key));

            if (is_iterable($value)) {
                $value = $this->replace($value);
            }

            if (! is_string($key)) {
                $result[$key] = $value;
                continue;
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
