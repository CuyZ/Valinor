<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Modifier\CamelCaseKeys;
use CuyZ\Valinor\Mapper\Source\Modifier\PathMapping;
use IteratorAggregate;
use SplFileObject;
use Traversable;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class Source implements IteratorAggregate
{
    private function __construct(
        /** @var iterable<mixed> */
        private iterable $delegate
    ) {}

    /**
     * @param iterable<mixed> $data
     */
    public static function iterable(iterable $data): Source
    {
        return new Source($data);
    }

    /**
     * @param array<mixed> $data
     */
    public static function array(array $data): Source
    {
        return new Source($data);
    }

    /**
     * @throws InvalidSource
     */
    public static function json(string $jsonSource): Source
    {
        return new Source(new JsonSource($jsonSource));
    }

    /**
     * @throws InvalidSource
     */
    public static function yaml(string $yamlSource): Source
    {
        return new Source(new YamlSource($yamlSource));
    }

    public static function file(SplFileObject $file): Source
    {
        return new Source(new FileSource($file));
    }

    public function camelCaseKeys(): Source
    {
        return new Source(new CamelCaseKeys($this));
    }

    /**
     * @param array<string> $map
     */
    public function map(array $map): Source
    {
        return new Source(new PathMapping($this, $map));
    }

    public function getIterator(): Traversable
    {
        yield from $this->delegate;
    }
}
