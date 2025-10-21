<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Type\ObjectType;

use function array_pop;
use function in_array;
use function strlen;

/** @internal */
final class TypeDumpContext
{
    private const MAX_LENGTH = 150;

    public function __construct(
        private string $signature = '',
        /** @var list<string> */
        private array $retainedTypes = [],
    ) {}

    public function write(string $content): self
    {
        return new self($this->signature . $content, $this->retainedTypes);
    }

    public function isTooLong(): bool
    {
        return strlen($this->signature) > self::MAX_LENGTH;
    }

    public function retain(ObjectType $type): self
    {
        return new self($this->signature, [...$this->retainedTypes, $type->toString()]);
    }

    public function forgetLastRetained(): self
    {
        $seenTypes = $this->retainedTypes;
        array_pop($seenTypes);

        return new self($this->signature, $seenTypes);
    }

    public function typeIsRetained(ObjectType $type): bool
    {
        return in_array($type->toString(), $this->retainedTypes, true);
    }

    public function read(): string
    {
        return $this->signature;
    }
}
