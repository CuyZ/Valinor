<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

interface ObjectBuilder
{
    /**
     * @param mixed $source
     * @return iterable<Argument>
     */
    public function describeArguments($source): iterable;

    /**
     * @param array<string, mixed> $arguments
     */
    public function build(array $arguments): object;
}
