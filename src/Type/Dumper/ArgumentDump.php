<?php

namespace CuyZ\Valinor\Type\Dumper;

/** @internal */
final class ArgumentDump
{
    public function __construct(
        public readonly int $weight,
        public readonly string $type
    ) {}
}
