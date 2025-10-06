<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ListMissingSubType extends RuntimeException implements InvalidType
{
    public function __construct(string $listType)
    {
        parent::__construct("The subtype is missing for `$listType<`.");
    }
}
