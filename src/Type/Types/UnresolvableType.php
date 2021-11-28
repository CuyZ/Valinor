<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;
use LogicException;

final class UnresolvableType extends LogicException implements Type
{
    public function __construct(string $message)
    {
        parent::__construct($message);
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
        return $this->message;
    }
}
