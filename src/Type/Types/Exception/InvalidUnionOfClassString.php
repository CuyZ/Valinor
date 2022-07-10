<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\UnionType;
use LogicException;

/** @internal */
final class InvalidUnionOfClassString extends LogicException implements InvalidType
{
    public function __construct(UnionType $type)
    {
        parent::__construct(
            "Type `{$type->toString()}` contains invalid class string element(s).",
            1648830951
        );
    }
}
