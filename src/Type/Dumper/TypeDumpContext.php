<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

final class TypeDumpContext
{
    private const MAX_LENGTH = 150;

    public function __construct(
        private readonly string $signature = '',
        private readonly int $depth = 0,
    ) {}

    public function write(string $content): self
    {
        return new self($this->signature . $content, $this->depth);
    }

    public function increaseDepth(): self
    {
        return new self($this->signature, $this->depth + 1);
    }

    public function decreaseDepth(): self
    {
        return new self($this->signature, $this->depth + 1);
    }

    public function isTooLong(): bool
    {
        return $this->depth > 1 && strlen($this->signature) > self::MAX_LENGTH;
    }

    public function read(): string
    {
        return $this->signature;
    }
}
