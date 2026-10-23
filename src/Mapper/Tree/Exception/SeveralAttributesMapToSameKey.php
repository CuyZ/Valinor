<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use LogicException;

/** @internal */
final class SeveralAttributesMapToSameKey extends LogicException
{
    public function __construct(string $sourceKey, string $firstElement, string $secondElement)
    {
        parent::__construct(
            "Attributes on `$firstElement` and `$secondElement` both map from the source key `$sourceKey`.",
        );
    }
}
