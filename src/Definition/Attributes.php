<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use IteratorAggregate;
use Traversable;

use function array_filter;
use function array_map;
use function count;
use function is_a;

/**
 * @internal
 *
 * @implements IteratorAggregate<AttributeDefinition>
 */
final class Attributes implements IteratorAggregate, Countable
{
    private static self $empty;

    /** @var list<AttributeDefinition> */
    private array $attributes;

    /**
     * @no-named-arguments
     */
    public function __construct(AttributeDefinition ...$attributes)
    {
        $this->attributes = $attributes;
    }

    public static function empty(): self
    {
        return self::$empty ??= new self();
    }

    public function forCallable(callable $callable): self
    {
        return new self(...array_map(
            fn (AttributeDefinition $attribute) => $attribute->forCallable($callable),
            $this->attributes,
        ));
    }

    public function has(string $className): bool
    {
        foreach ($this->attributes as $attribute) {
            if (is_a($attribute->class->type->className(), $className, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(AttributeDefinition): bool $callback
     */
    public function filter(callable $callback): self
    {
        return new self(
            ...array_filter($this->attributes, $callback)
        );
    }

    public function merge(self $other): self
    {
        $clone = clone $this;
        $clone->attributes = [...$this->attributes, ...$other->attributes];

        return $clone;
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * @return list<AttributeDefinition>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @return Traversable<AttributeDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->attributes;
    }
}
