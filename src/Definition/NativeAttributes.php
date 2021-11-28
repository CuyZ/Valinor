<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Definition\Exception\InvalidReflectionParameter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

use Traversable;

use function array_map;

final class NativeAttributes implements Attributes
{
    private AttributesContainer $delegate;

    /** @var array<ReflectionAttribute<object>> */
    private array $reflectionAttributes;

    public function __construct(Reflector $reflection)
    {
        $this->reflectionAttributes = $this->attributes($reflection);

        $attributes = array_map(
            static fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
            $this->reflectionAttributes
        );

        $this->delegate = new AttributesContainer(...$attributes);
    }

    public function has(string $className): bool
    {
        return $this->delegate->has($className);
    }

    public function ofType(string $className): iterable
    {
        return $this->delegate->ofType($className);
    }

    public function getIterator(): Traversable
    {
        yield from $this->delegate;
    }

    public function count(): int
    {
        return count($this->delegate);
    }

    /**
     * @return array<ReflectionAttribute<object>>
     */
    public function reflectionAttributes(): array
    {
        return $this->reflectionAttributes;
    }

    /**
     * @return array<ReflectionAttribute<object>>
     */
    private function attributes(Reflector $reflection): array
    {
        if ($reflection instanceof ReflectionClass) {
            return $reflection->getAttributes();
        }

        if ($reflection instanceof ReflectionProperty) {
            return $reflection->getAttributes();
        }

        if ($reflection instanceof ReflectionMethod) {
            return $reflection->getAttributes();
        }

        if ($reflection instanceof ReflectionParameter) {
            return $reflection->getAttributes();
        }

        throw new InvalidReflectionParameter($reflection);
    }
}
