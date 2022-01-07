<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

interface ObjectBuilder
{
    /**
     * @return iterable<Argument>
     */
    public function describeArguments(): iterable;

    /**
     * @param array<string, mixed> $arguments
     */
    public function build(array $arguments): object;
}
