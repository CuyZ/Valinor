<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use RuntimeException;

/** @internal */
final class ListClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(ListType|NonEmptyListType $listType)
    {
        parent::__construct("The closing bracket is missing for `{$listType->toString()}`.");
    }
}
