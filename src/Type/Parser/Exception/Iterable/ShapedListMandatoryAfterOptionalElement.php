<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function implode;

/** @internal */
final class ShapedListMandatoryAfterOptionalElement extends RuntimeException implements InvalidType
{
    public function __construct(int $index, ShapedArrayElement ...$elements)
    {
        $parts = [];

        foreach ($elements as $element) {
            $parts[] = $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString();
        }

        $signature = 'list{' . implode(', ', $parts) . '}';

        parent::__construct(
            "Mandatory element at position $index cannot follow an optional element in shaped list `$signature`.",
        );
    }
}
