<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Type\ClassType;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ObjectImplementationNotRegistered extends RuntimeException
{
    /**
     * @param non-empty-array<string, ClassType> $allowed
     */
    public function __construct(string $implementation, string $name, array $allowed)
    {
        $allowed = implode('`, `', array_map(fn (ClassType $type) => $type->toString(), $allowed));

        parent::__construct(
            "Invalid implementation `$implementation` for `$name`, it should be one of `$allowed`.",
        );
    }
}
