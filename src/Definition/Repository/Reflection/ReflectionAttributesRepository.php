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
use Reflector;

use function array_map;
use function array_values;

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
        $attributes = array_filter(
            $reflection->getAttributes(),
            function (ReflectionAttribute $attribute) {
                foreach ($this->allowedAttributes as $allowedAttribute) {
                    if (is_a($attribute->getName(), $allowedAttribute, true)) {
                        return $this->attributeCanBeInstantiated($attribute);
                    }
                }

                return Reflection::class($attribute->getName())->getAttributes(AsConverter::class) !== []
                    || Reflection::class($attribute->getName())->getAttributes(AsTransformer::class) !== [];
            },
        );

        return array_values(array_map(
            fn (ReflectionAttribute $attribute) => new AttributeDefinition(
                $this->classDefinitionRepository->for(new NativeClassType($attribute->getName())),
                array_values($attribute->getArguments()),
            ),
            $attributes,
        ));
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
}
