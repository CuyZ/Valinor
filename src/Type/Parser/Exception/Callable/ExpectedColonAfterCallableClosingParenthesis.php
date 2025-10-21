<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Callable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ExpectedColonAfterCallableClosingParenthesis extends RuntimeException implements InvalidType
{
    /**
     * @param list<Type> $parameters
     */
    public function __construct(array $parameters)
    {
        $parameters = implode(', ', array_map(fn (Type $type) => $type->toString(), $parameters));

        parent::__construct("Expected `:` to define return type after `callable($parameters)`.");
    }
}
