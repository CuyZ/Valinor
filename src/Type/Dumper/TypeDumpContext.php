<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

/** @internal */
final class TypeDumpContext
{
    private const MAX_LENGTH = 150;

    public function __construct(
        private readonly string $signature = '',
    ) {}

    public function write(string $content): self
    {
        return new self($this->signature . $content);
    }

    public function isTooLong(): bool
    {
        return strlen($this->signature) > self::MAX_LENGTH;
    }

    public function read(): string
    {
        return $this->signature;
    }
}
