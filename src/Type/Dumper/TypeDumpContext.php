<?php

namespace CuyZ\Valinor\Type\Dumper;

/** @internal */
final class TypeDumpContext
{
    public function __construct(
        public readonly int $length = 0,
        public readonly int $weight = 0
    ) {}

    public function addWeight(int $weight): self
    {
        return new self($this->length, $this->weight + $weight);
    }

    public function addLength(int $length): self
    {
        return new self($this->length + $length, $this->weight);
    }
}
