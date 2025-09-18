<?php

namespace CuyZ\Valinor\Type\Dumper;

/** @internal */
final class TypeDumpContext
{
    private function __construct(
        public readonly int $deep = 0,
        public readonly int $length = 0,
        public readonly int $weight = 0
    ) {}

    public static function root(): self
    {
        return new self(0, 0, 0);
    }

    public function nextDepth(): self
    {
        return new self($this->deep + 1, $this->length, $this->weight);
    }

    public function addWeight(int $weight): self
    {
        return new self($this->deep, $this->length, $this->weight + $weight);
    }

    public function addLength(int $length): self
    {
        return new self($this->deep, $this->length + $length, $this->weight);
    }
}
