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

use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_INVALID_UTF8_IGNORE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_NUMERIC_CHECK;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const JSON_THROW_ON_ERROR;

/**
 * @api
 *
 * @implements Normalizer<string>
 */
final class JsonNormalizer implements Normalizer
{
    public const JSON_HEX_QUOT = JSON_HEX_QUOT;
    public const JSON_HEX_TAG = JSON_HEX_TAG;
    public const JSON_HEX_AMP = JSON_HEX_AMP;
    public const JSON_HEX_APOS = JSON_HEX_APOS;
    public const JSON_INVALID_UTF8_IGNORE = JSON_INVALID_UTF8_IGNORE;
    public const JSON_INVALID_UTF8_SUBSTITUTE = JSON_INVALID_UTF8_SUBSTITUTE;
    public const JSON_NUMERIC_CHECK = JSON_NUMERIC_CHECK;
    public const JSON_PRESERVE_ZERO_FRACTION = JSON_PRESERVE_ZERO_FRACTION;
    public const JSON_UNESCAPED_LINE_TERMINATORS = JSON_UNESCAPED_LINE_TERMINATORS;
    public const JSON_UNESCAPED_SLASHES = JSON_UNESCAPED_SLASHES;
    public const JSON_UNESCAPED_UNICODE = JSON_UNESCAPED_UNICODE;
    public const JSON_THROW_ON_ERROR = JSON_THROW_ON_ERROR;
    private const ACCEPTABLE_JSON_OPTIONS = self::JSON_HEX_QUOT
    | self::JSON_HEX_TAG
    | self::JSON_HEX_AMP
    | self::JSON_HEX_APOS
    | self::JSON_INVALID_UTF8_IGNORE
    | self::JSON_INVALID_UTF8_SUBSTITUTE
    | self::JSON_NUMERIC_CHECK
    | self::JSON_PRESERVE_ZERO_FRACTION
    | self::JSON_UNESCAPED_LINE_TERMINATORS
    | self::JSON_UNESCAPED_SLASHES
    | self::JSON_UNESCAPED_UNICODE
    | self::JSON_THROW_ON_ERROR;

    /**
     * @param int-mask-of<JsonNormalizer::JSON_*> $jsonEncodingOptions
     */
    public function __construct(
        private RecursiveTransformer $transformer,
        public readonly int $jsonEncodingOptions = self::JSON_THROW_ON_ERROR,
    ) {
        assert(
            ($this->jsonEncodingOptions & JSON_THROW_ON_ERROR) === JSON_THROW_ON_ERROR,
            'JSON encoding options always have to contain JSON_THROW_ON_ERROR.',
        );
    }

    /**
     * @param int-mask-of<JsonNormalizer::JSON_*> $options
     */
    public function withOptions(int $options): self
    {
        /**
         * SA tools are not able to infer that we end up having only accepted options here. Therefore, inlining the
         * type for now should be okayish.
         * Might be fixed with https://github.com/phpstan/phpstan/issues/9384 for phpstan but psalm does have some
         * (not all) issues as well.
         *
         * @var int-mask-of<JsonNormalizer::JSON_*> $acceptedOptions
         */
        $acceptedOptions = (self::ACCEPTABLE_JSON_OPTIONS & $options) | self::JSON_THROW_ON_ERROR;
        return new self($this->transformer, $acceptedOptions);
    }

    public function normalize(mixed $value): string
    {
        $value = $this->transformer->transform($value);

        /** @var resource $resource */
        $resource = fopen('php://memory', 'w');

        (new JsonFormatter($resource, $this->jsonEncodingOptions))->format($value);

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

        return new StreamNormalizer($this->transformer, new JsonFormatter($resource, $this->jsonEncodingOptions));
    }
}
