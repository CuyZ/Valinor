<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use stdClass;

final class FakeObjectType implements ObjectType
{
    private Type $matching;

    /** @var class-string */
    private string $accepted;

    /**
     * @param class-string $className
     */
    public function __construct(private string $className = stdClass::class) {}

    /**
     * @param class-string $className
     */
    public static function accepting(string $className): self
    {
        $instance = new self();
        $instance->accepted = $className;

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

    public function accepts(mixed $value): bool
    {
        return isset($this->accepted) && $value instanceof $this->accepted;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        if (! isset($this->accepted)) {
            return Node::value(false);
        }

        return $node->instanceOf($this->accepted);
    }

    public function matches(Type $other): bool
    {
        return $other === ($this->matching ?? null);
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function nativeType(): ObjectType
    {
        return $this;
    }

    public function toString(): string
    {
        return $this->className;
    }
}
