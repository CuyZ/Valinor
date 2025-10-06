<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use RuntimeException;

/** @internal */
final class CircularReferenceFoundDuringNormalization extends RuntimeException
{
    public function __construct(object $object)
    {
        $class = $object::class;

        parent::__construct(
            "A circular reference was detected with an object of type `$class`. Circular references are not supported by the normalizer.",
        );
    }
}
