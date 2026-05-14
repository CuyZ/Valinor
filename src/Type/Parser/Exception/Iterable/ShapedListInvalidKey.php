<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_filter;
use function array_map;
use function implode;

/** @internal */
final class ShapedListInvalidKey extends RuntimeException implements InvalidType
{
    public function __construct(Type $key, int $expectedIndex, ShapedArrayElement ...$elements)
    {
        $hasOptional = array_filter($elements, fn (ShapedArrayElement $element) => $element->isOptional()) !== [];
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts) . ($parts !== [] ? ', ' : '') . $key->toString() . ':...}';

        parent::__construct(
            "Key `{$key->toString()}` is not valid for a list element; expected sequential integer key `$expectedIndex` in shaped list `$signature`.",
        );
    }
}
