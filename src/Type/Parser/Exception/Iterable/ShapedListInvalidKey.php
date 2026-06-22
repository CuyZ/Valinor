<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ShapedListInvalidKey extends RuntimeException implements InvalidType
{
    public function __construct(Type $key, int $expectedIndex)
    {
        parent::__construct("Key `{$key->toString()}` is not valid for a list element, expected sequential integer key `$expectedIndex`.");
    }
}
