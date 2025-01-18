<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

/** @internal */
interface ObjectBuilder
{
    public function describeArguments(): Arguments;

    /**
     * @param array<string, mixed> $arguments
     */
    public function build(array $arguments): object;

    /**
     * @return non-empty-string
     */
    public function signature(): string;

    /**
     * @return non-empty-string
     */
    public function signatureForArgument(string $argumentName): string;
}
