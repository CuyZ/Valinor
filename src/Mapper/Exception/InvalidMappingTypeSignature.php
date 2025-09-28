<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Type\Types\UnresolvableType;
use RuntimeException;

use function lcfirst;

/** @internal */
final class InvalidMappingTypeSignature extends RuntimeException
{
    public function __construct(UnresolvableType $unresolvableType)
    {
        parent::__construct(
            "Could not parse the type `{$unresolvableType->toString()}` that should be mapped: " . lcfirst($unresolvableType->message()),
        );
    }
}
