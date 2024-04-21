<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Formatter\Compiler\FormatterCompiler;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\JsonFormatterCompiler;
use CuyZ\Valinor\Normalizer\Formatter\Exception\CannotFormatInvalidTypeToJson;
use Generator;

use function array_is_list;
use function fwrite;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_null;
use function is_scalar;
use function json_encode;

/** @internal */
final class JsonStreamFormatter implements Formatter
{
    /**
     * @param resource $resource
     */
    public function __construct(
        public readonly mixed $resource,
        public readonly int $jsonEncodingOptions,
    ) {}

    /**
     * @return resource
     */
    public function format(mixed $value): mixed
    {
        $this->formatRecursively($value);

        return $this->resource;
    }

    public function compiler(): FormatterCompiler
    {
        return new JsonFormatterCompiler();
    }

    private function formatRecursively(mixed $value): void
    {
        if (is_null($value)) {
            $this->write('null');
        } elseif (is_bool($value)) {
            $this->write($value ? 'true' : 'false');
        } elseif (is_scalar($value)) {
            /**
             * @phpstan-ignore-next-line / Due to the new json encoding options feature, it is not possible to let SA
             *                             tools understand that JSON_THROW_ON_ERROR is always set.
             */
            $this->write(json_encode($value, $this->jsonEncodingOptions));
        } elseif (is_iterable($value)) {
            // Note: when a generator is formatted, it is considered as a list
            // if its first key is 0. This is done early because the first JSON
            // character for an array differs from the one for an object, and we
            // need to know that before actually looping on the generator.
            //
            // For generators having a first key of 0 and inconsistent keys
            // afterward, this leads to a JSON array being written, while it
            // should have been an object. This is a trade-off we accept,
            // considering most generators starting at 0 are actually lists.
            $isList = ($value instanceof Generator && $value->key() === 0)
                || (is_array($value) && array_is_list($value));

            $isFirst = true;

            $this->write($isList ? '[' : '{');

            foreach ($value as $key => $val) {
                if (! $isFirst) {
                    $this->write(',');
                }

                $isFirst = false;

                if (! $isList) {
                    $key = json_encode((string)$key, $this->jsonEncodingOptions);

                    $this->write($key . ':');
                }

                $this->formatRecursively($val);
            }

            $this->write($isList ? ']' : '}');
        } else {
            throw new CannotFormatInvalidTypeToJson($value);
        }
    }

    private function write(string $content): void
    {
        fwrite($this->resource, $content);
    }
}
