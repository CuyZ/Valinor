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

    private Type $matching;

    /** @var object[] */
    private array $accepting;

    /**
     * @param class-string $className
     */
    public function __construct(string $className = stdClass::class)
    {
        $this->className = $className;
    }

    public static function accepting(object ...$objects): self
    {
        $instance = new self();
        $instance->accepting = $objects;

        return $instance;
    }

    public static function matching(Type $other): self
    {
        $instance = new self();
        $instance->matching = $other;

        return $instance;
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
        return isset($this->accepting) && in_array($value, $this->accepting, true);
    }

    public function matches(Type $other): bool
    {
        return $other === ($this->matching ?? null);
    }

    public function __toString(): string
    {
        return $this->className;
    }
}
