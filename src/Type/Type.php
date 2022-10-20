<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface Type
{
    public function accepts(mixed $value): bool;

    public function matches(self $other): bool;

    public function toString(): string;
}
