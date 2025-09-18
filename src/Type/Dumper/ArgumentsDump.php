<?php

namespace CuyZ\Valinor\Type\Dumper;

/** @internal */
final class ArgumentsDump
{
    public function __construct(
        public readonly int $weight,
        public readonly string $type
    ) {}
}
