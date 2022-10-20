<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Error;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Traversable;

use function array_map;

/** @internal */
final class NativeAttributes implements Attributes
{
    private AttributesContainer $delegate;

    /** @var array<ReflectionAttribute<object>> */
    private array $reflectionAttributes;

    /**
     * @param ReflectionClass<object>|ReflectionProperty|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflection
     */
    public function __construct(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflection)
    {
        $this->reflectionAttributes = $reflection->getAttributes();

        $attributes = array_filter(
            array_map(
                static function (ReflectionAttribute $attribute) {
                    try {
                        return $attribute->newInstance();
                    } catch (Error) {
                        // Race condition when the attribute is affected to a property/parameter
                        // that was PROMOTED, in this case the attribute will be applied to both
                        // ParameterReflection AND PropertyReflection, BUT the target arg inside the attribute
                        // class is configured to support only ONE of them (parameter OR property)
                        // https://wiki.php.net/rfc/constructor_promotion#attributes for more details.
                        // Ignore attribute if the instantiation failed.
                        return null;
                    }
                },
                $this->reflectionAttributes,
            ),
        );

        $this->delegate = new AttributesContainer(...$attributes);
    }

    public function has(string $className): bool
    {
        return $this->delegate->has($className);
    }

    public function ofType(string $className): array
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
}
