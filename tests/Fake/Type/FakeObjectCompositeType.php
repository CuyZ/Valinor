<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use stdClass;

final class FakeObjectCompositeType implements ObjectType, CompositeType
{
    /**
     * @param class-string $className
     * @param array<string, Type> $generics
     */
    public function __construct(private string $className = stdClass::class, private array $generics = [])
    {
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts(mixed $value): bool
    {
        return true;
    }

    public function matches(Type $other): bool
    {
        return true;
    }

    public function traverse(): iterable
    {
        yield from $this->generics;
    }

    public function toString(): string
    {
        return $this->className;
    }
}
