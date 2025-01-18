<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Compiler\Native\CompliantNode;

/** @internal */
interface Type
{
    public function accepts(mixed $value): bool;

    public function compiledAccept(CompliantNode $node): CompliantNode;

    public function matches(self $other): bool;

    public function toString(): string;
}
