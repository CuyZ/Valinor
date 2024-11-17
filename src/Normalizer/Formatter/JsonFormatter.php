<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Formatter\Exception\CannotFormatInvalidTypeToJson;
use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use Generator;

use function array_is_list;
use function assert;
use function fwrite;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_null;
use function is_scalar;
use function json_encode;

use const JSON_FORCE_OBJECT;

/** @internal */
final class JsonFormatter implements StreamFormatter
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private mixed $resource,
        private int $jsonEncodingOptions,
    ) {}

    public function format(mixed $value): void
    {
        $this->formatRecursively($value, 1);
    }

    private function formatRecursively(mixed $value, int $depth): void
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
        } elseif ($value instanceof EmptyObject) {
            $this->write('{}');
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
            $isList = ! ($this->jsonEncodingOptions & JSON_FORCE_OBJECT)
                && (
                    ($value instanceof Generator && $value->key() === 0)
                    || (is_array($value) && array_is_list($value))
                );

            $isFirst = true;

            $this->write($isList ? '[' : '{');

            foreach ($value as $key => $val) {
                $chunk = '';

                if (! $isFirst) {
                    $chunk = ',';
                }

                if ($this->jsonEncodingOptions & JSON_PRETTY_PRINT) {
                    $chunk .= PHP_EOL . str_repeat('    ', $depth);
                }

                $isFirst = false;

                if (! $isList) {
                    assert(is_scalar($key));

                    $key = json_encode((string)$key, $this->jsonEncodingOptions);

                    $chunk .= $key . ':';

                    if ($this->jsonEncodingOptions & JSON_PRETTY_PRINT) {
                        $chunk .= ' ';
                    }
                }

                $this->write($chunk);

                $this->formatRecursively($val, $depth + 1);
            }

            $chunk = '';

            if ($this->jsonEncodingOptions & JSON_PRETTY_PRINT) {
                $chunk = PHP_EOL . str_repeat('    ', $depth - 1);
            }

            $chunk .= $isList ? ']' : '}';

            $this->write($chunk);
        } else {
            throw new CannotFormatInvalidTypeToJson($value);
        }
    }

    public function resource(): mixed
    {
        return $this->resource;
    }

    private function write(string $content): void
    {
        fwrite($this->resource, $content);
    }
}
