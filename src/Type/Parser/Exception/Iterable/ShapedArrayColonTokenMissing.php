<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function str_ends_with;

/** @internal */
final class ShapedArrayColonTokenMissing extends RuntimeException implements InvalidType
{
    public function __construct(string $signature, Type $type)
    {
        if (! str_ends_with($signature, '{')) {
            $signature .= ', ';
        }

        $signature .= "{$type->toString()}?";

        parent::__construct("Missing colon in `$signature`.");
    }
}
