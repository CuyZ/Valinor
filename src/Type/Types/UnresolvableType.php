<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;
use LogicException;

/** @api */
final class UnresolvableType extends LogicException implements Type
{
    private string $rawType;

    public function __construct(string $rawType, string $message)
    {
        parent::__construct($message);

        $this->rawType = $rawType;
    }

    public function accepts($value): bool
    {
        throw $this;
    }

    public function matches(Type $other): bool
    {
        throw $this;
    }

    public function __toString(): string
    {
        return $this->rawType;
    }
}
