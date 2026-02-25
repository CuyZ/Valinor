<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Formatter\Exception\CannotFormatInvalidTypeToJson;
use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use Generator;

use function array_is_list;
use function assert;
use function fwrite;
use function is_bool;
use function is_iterable;
use function is_null;
use function is_scalar;
use function json_encode;

use function str_repeat;

use const JSON_FORCE_OBJECT;

/** @internal */
final readonly class JsonFormatter
{
    private bool $prettyPrint;

    private bool $forceObject;

    public function __construct(
        /** @var resource */
        private mixed $resource,
        private int $jsonEncodingOptions,
    ) {
        $this->prettyPrint = (bool)($this->jsonEncodingOptions & JSON_PRETTY_PRINT);
        $this->forceObject = (bool)($this->jsonEncodingOptions & JSON_FORCE_OBJECT);
    }

    /**
     * @return resource
     */
    public function format(mixed $value): mixed
    {
        $this->formatRecursively($value, 1);

        return $this->resource;
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
            if ($this->forceObject) {
                $isList = false;
            } elseif ($value instanceof Generator) {
                if (! $value->valid()) {
                    $this->write('[]');
                    return;
                }

                $isList = $value->key() === 0;
            } else {
                /** @var array<mixed> $value At this point we know this is an array */
                $isList = array_is_list($value);
            }

            $isFirst = true;

            $this->write($isList ? '[' : '{');

            foreach ($value as $key => $val) {
                $chunk = '';

                if (! $isFirst) {
                    $chunk = ',';
                }

                if ($this->prettyPrint) {
                    $chunk .= PHP_EOL . str_repeat('    ', $depth);
                }

                $isFirst = false;

                if (! $isList) {
                    assert(is_scalar($key));

                    $key = json_encode((string)$key, $this->jsonEncodingOptions);

                    $chunk .= $key . ':';

                    if ($this->prettyPrint) {
                        $chunk .= ' ';
                    }
                }

                $this->write($chunk);

                $this->formatRecursively($val, $depth + 1);
            }

            $chunk = '';

            if ($this->prettyPrint) {
                $chunk = PHP_EOL . str_repeat('    ', $depth - 1);
            }

            $chunk .= $isList ? ']' : '}';

            $this->write($chunk);
        } else {
            throw new CannotFormatInvalidTypeToJson($value);
        }
    }

    private function write(string $content): void
    {
        fwrite($this->resource, $content);
    }
}
