<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use Error;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

use function is_a;
use function is_array;
use function is_scalar;

/** @internal */
final class ReflectionAttributesRepository implements AttributesRepository
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        /** @var list<class-string> */
        private array $allowedAttributes,
    ) {}

    public function for(Reflector $reflection): array
    {
        $attributes = [];

        foreach ($reflection->getAttributes() as $key => $attribute) {
            if (! $this->attributeIsAllowed($attribute) || ! $this->attributeCanBeInstantiated($attribute)) {
                continue;
            }

            $arguments = $attribute->getArguments();

            if (! self::containOnlyScalar($arguments)) {
                $arguments = null;
            }

            /** @var null|list<array<scalar>|scalar> $arguments */
            $attributes[] = new AttributeDefinition(
                $this->classDefinitionRepository->for(new NativeClassType($attribute->getName())),
                $arguments,
                match ($reflection::class) {
                    ReflectionClass::class => ['class', $reflection->name],
                    ReflectionProperty::class => ['property', $reflection->getDeclaringClass()->name, $reflection->name],
                    ReflectionMethod::class => ['method', $reflection->getDeclaringClass()->name, $reflection->name],
                    ReflectionParameter::class => $reflection->getDeclaringFunction()->isClosure()
                        ? ['closureParameter', $reflection->getPosition()]
                        // @phpstan-ignore property.nonObject ($reflection->getDeclaringClass() is not null)
                        : ['methodParameter', $reflection->getDeclaringClass()->name, $reflection->getDeclaringFunction()->name, $reflection->getPosition()],
                    default => ['closure'],
                },
                $key,
            );
        }

        return $attributes;
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    private function attributeIsAllowed(ReflectionAttribute $attribute): bool
    {
        foreach ($this->allowedAttributes as $allowedAttribute) {
            if (is_a($attribute->getName(), $allowedAttribute, true)) {
                return true;
            }
        }

        return Reflection::class($attribute->getName())->getAttributes(AsConverter::class) !== []
            || Reflection::class($attribute->getName())->getAttributes(AsTransformer::class) !== [];
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    private function attributeCanBeInstantiated(ReflectionAttribute $attribute): bool
    {
        try {
            $attribute->newInstance();

            return true;
        } catch (Error) {
            // Race condition when the attribute is affected to a property/parameter
            // that was PROMOTED, in this case the attribute will be applied to both
            // ParameterReflection AND PropertyReflection, BUT the target arg inside the attribute
            // class is configured to support only ONE of them (parameter OR property)
            // https://wiki.php.net/rfc/constructor_promotion#attributes for more details.
            // Ignore attribute if the instantiation failed.
            return false;
        }
    }

    private static function containOnlyScalar(mixed $value): bool
    {
        if (is_scalar($value)) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $subValue) {
                if (! self::containOnlyScalar($subValue)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
