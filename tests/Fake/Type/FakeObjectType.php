<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use stdClass;

use function in_array;

final class FakeObjectType implements ObjectType
{
    /** @var class-string */
    private string $className;

    /** @var Type[] */
    private array $matching = [];

    /** @var mixed[] */
    private array $accepting = [];

    /**
     * @param class-string $className
     */
    public function __construct(string $className = stdClass::class)
    {
        $this->className = $className;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return [];
    }

    public function accepts($value): bool
    {
        return in_array($value, $this->accepting, true);
    }

    public function matches(Type $other): bool
    {
        return in_array($other, $this->matching, true);
    }

    public function willAccept(object $object): void
    {
        $this->accepting[] = $object;
    }

    public function willMatch(Type ...$others): void
    {
        $this->matching = [...$this->matching, ...$others];
    }

    public function __toString(): string
    {
        return $this->className;
    }
}
