<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Definition\Exception\InvalidReflectionParameter;
use CuyZ\Valinor\Utility\Singleton;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Traversable;

final class DoctrineAnnotations implements Attributes
{
    private AttributesContainer $delegate;

    private Reflector $reflection;

    public function __construct(Reflector $reflection)
    {
        $this->reflection = $reflection;

        $this->delegate = new AttributesContainer(...$this->attributes($reflection));
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

    public function reflection(): Reflector
    {
        return $this->reflection;
    }

    /**
     * @return object[]
     */
    private function attributes(Reflector $reflection): array
    {
        if ($reflection instanceof ReflectionClass) {
            return Singleton::annotationReader()->getClassAnnotations($reflection);
        }

        if ($reflection instanceof ReflectionProperty) {
            return Singleton::annotationReader()->getPropertyAnnotations($reflection);
        }

        if ($reflection instanceof ReflectionMethod) {
            return Singleton::annotationReader()->getMethodAnnotations($reflection);
        }

        if ($reflection instanceof ReflectionParameter) {
            return [];
        }

        throw new InvalidReflectionParameter($reflection);
    }
}
