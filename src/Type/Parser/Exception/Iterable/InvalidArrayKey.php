<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class InvalidArrayKey extends RuntimeException implements InvalidType
{
    /**
     * @param non-empty-array<Type> $invalidSubTypes
     */
    public function __construct(array $invalidSubTypes)
    {
        $invalidSubTypes = array_map(static fn (Type $type) => $type->toString(), $invalidSubTypes);

        parent::__construct(
            'Invalid array-key element(s) `' . implode('`, `', $invalidSubTypes) . "`, each element must be an integer or a string.",
        );
    }
}
