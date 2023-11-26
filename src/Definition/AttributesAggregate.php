<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Traversable;

use function array_sum;

/** @internal */
final class AttributesAggregate implements Attributes
{
    /** @var array<Attributes> */
    private array $attributes;

    public function __construct(Attributes ...$attributes)
    {
        $this->attributes = $attributes;
    }

    public function has(string $className): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->has($className)) {
                return true;
            }
        }

        return false;
    }

    public function ofType(string $className): array
    {
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            $attributes = [...$attributes, ...$attribute->ofType($className)];
        }

        return $attributes;
    }

    public function count(): int
    {
        return array_sum(array_map(fn (Attributes $attributes) => count($attributes), $this->attributes));
    }

    /**
     * @return Traversable<object>
     */
    public function getIterator(): Traversable
    {
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            $attributes = [...$attributes, ...$attribute];
        }

        yield from $attributes;
    }
}
