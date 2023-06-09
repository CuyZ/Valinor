<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use stdClass;

use function array_values;

final class FakeObjectCompositeType implements ObjectType, CompositeType
{
    public function __construct(
        /** @var class-string */
        private string $className = stdClass::class,
        /** @var array<string, Type> */
        private array $generics = []
    ) {}

    public function className(): string
    {
        return $this->className;
    }

    public function accepts(mixed $value): bool
    {
        return true;
    }

    public function matches(Type $other): bool
    {
        return true;
    }

    public function traverse(): array
    {
        return array_values($this->generics);
    }

    public function toString(): string
    {
        return $this->className;
    }
}
