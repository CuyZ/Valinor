<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class SourceValueWasNotFilled extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(
            "Source was not filled at path `$path`; use method `\$node->sourceFilled()`.",
            1657466107
        );
    }
}
