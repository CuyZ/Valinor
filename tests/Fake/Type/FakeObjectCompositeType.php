<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use stdClass;

final class FakeObjectCompositeType implements ObjectType, CompositeType
{
    /** @var class-string */
    private string $className;

    /** @var array<string, Type> */
    private array $generics;

    /**
     * @param class-string $className
     * @param array<string, Type> $generics
     */
    public function __construct(string $className = stdClass::class, array $generics = [])
    {
        $this->className = $className;
        $this->generics = $generics;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts($value): bool
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

    public function __toString(): string
    {
        return $this->className;
    }
}
