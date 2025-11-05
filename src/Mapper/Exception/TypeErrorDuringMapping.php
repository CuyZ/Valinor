<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Type\Type;
use LogicException;

use function lcfirst;

/** @internal */
final class TypeErrorDuringMapping extends LogicException
{
    public function __construct(Type $type, MappingLogicalException $exception)
    {
        parent::__construct(
            "Error while trying to map to `{$type->toString()}`: " . lcfirst($exception->getMessage()),
            previous: $exception,
        );
    }
}
