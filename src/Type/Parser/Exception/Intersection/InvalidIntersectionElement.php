<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Intersection;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class InvalidIntersectionElement extends RuntimeException implements InvalidType
{
    /**
     * @param non-empty-array<Type> $types
     * @param non-empty-array<Type> $invalidTypes
     */
    public function __construct(array $types, array $invalidTypes)
    {
        $types = array_map(static fn (Type $type) => $type->toString(), $types);
        $invalidTypes = array_map(static fn (Type $type) => $type->toString(), $invalidTypes);

        $signature = implode('&', $types);
        $invalidSignature = implode('`, `', $invalidTypes);

        parent::__construct(
            "Invalid types in intersection `$signature`, each element must be a class name or an interface name but found `$invalidSignature`.",
        );
    }
}
