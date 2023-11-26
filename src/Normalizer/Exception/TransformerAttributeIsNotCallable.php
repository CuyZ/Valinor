<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use LogicException;

/** @internal */
final class TransformerAttributeIsNotCallable extends LogicException
{
    public function __construct(string $attributeClass)
    {
        parent::__construct(
            "Transformer attribute `$attributeClass` is not callable, it should provide a method `__invoke` with at least one parameter.",
            1700858616,
        );
    }
}
