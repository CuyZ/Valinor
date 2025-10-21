<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Union;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use LogicException;

use function array_map;
use function implode;

/** @internal */
final class InvalidClassStringElements extends LogicException implements InvalidType
{
    /**
     * @param array<Type> $subTypes
     * @param array<Type> $invalidSubTypes
     */
    public function __construct(array $subTypes, array $invalidSubTypes)
    {
        $subTypes = array_map(static fn (Type $type) => $type->toString(), $subTypes);
        $invalidSubTypes = implode('`, `', array_map(static fn (Type $type) => $type->toString(), $invalidSubTypes));

        $signature = 'class-string<' . implode('|', $subTypes) . '>';

        parent::__construct(
            "Invalid class string `$signature`, each element must be a class name or an interface name but found `$invalidSubTypes`.",
        );
    }
}
