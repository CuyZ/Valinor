<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ShapedArrayClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(string $signature, Type|null|false $unsealedType = null)
    {
        if ($unsealedType === false) {
            $signature .= ', ...';
        } elseif ($unsealedType instanceof Type) {
            $signature .= ', ...' . $unsealedType->toString();
        }

        parent::__construct("Missing closing curly bracket in `$signature`.");
    }
}
