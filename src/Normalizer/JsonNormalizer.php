<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Formatter\JsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\RecursiveTransformer;

use RuntimeException;

use function fclose;
use function fopen;
use function get_debug_type;
use function is_resource;
use function stream_get_contents;

/**
 * @api
 *
 * @implements Normalizer<string>
 */
final class JsonNormalizer implements Normalizer
{
    public function __construct(
        private RecursiveTransformer $transformer,
    ) {}

    public function normalize(mixed $value): string
    {
        $value = $this->transformer->transform($value);

        /** @var resource $resource */
        $resource = fopen('php://memory', 'w');

        (new JsonFormatter($resource))->format($value);

        rewind($resource);

        /** @var string */
        $json = stream_get_contents($resource);

        fclose($resource);

        return $json;
    }

    /**
     * Returns a new normalizer that will write the JSON to the given resource
     * instead of returning a string.
     *
     * A benefit of streaming the data to a PHP resource is that it may be more
     * memory-efficient when using generators — for instance when querying a
     * database:
     *
     * ```php
     * // In this example, we assume that the result of the query below is a
     * // generator, every entry will be yielded one by one, instead of
     * // everything being loaded in memory at once.
     * $users = $database->execute('SELECT * FROM users');
     *
     * $file = fopen('path/to/some_file.json', 'w');
     *
     * $normalizer = (new \CuyZ\Valinor\MapperBuilder())
     *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
     *     ->streamTo($file);
     *
     * // Even if there are thousands of users, memory usage will be kept low
     * // when writing JSON into the file.
     * $normalizer->normalize($users);
     * ```
     *
     * @param resource $resource
     */
    public function streamTo(mixed $resource): StreamNormalizer
    {
        // This check is there to help people that do not use static analyzers.
        // @phpstan-ignore-next-line
        if (! is_resource($resource)) {
            throw new RuntimeException('Expected a valid resource, got ' . get_debug_type($resource));
        }

        return new StreamNormalizer($this->transformer, new JsonFormatter($resource));
    }
}
