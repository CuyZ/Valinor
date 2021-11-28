<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionMethod;
use ReflectionProperty;

use function array_map;

final class ReflectionClassDefinitionRepository implements ClassDefinitionRepository
{
    private AttributesRepository $attributesFactory;

    private ReflectionPropertyDefinitionBuilder $propertyBuilder;

    private ReflectionMethodDefinitionBuilder $methodBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->attributesFactory = $attributesRepository;

        $this->propertyBuilder = new ReflectionPropertyDefinitionBuilder($typeParserFactory, $attributesRepository);
        $this->methodBuilder = new ReflectionMethodDefinitionBuilder($typeParserFactory, $attributesRepository);
    }

    public function for(ClassSignature $signature): ClassDefinition
    {
        $reflection = Reflection::class($signature->className());

        $properties = array_map(
            fn (ReflectionProperty $property) => $this->propertyBuilder->for($signature, $property),
            $reflection->getProperties()
        );

        $methods = array_map(
            fn (ReflectionMethod $method) => $this->methodBuilder->for($signature, $method),
            $reflection->getMethods()
        );

        return new ClassDefinition(
            $signature->className(),
            $signature->toString(),
            $this->attributesFactory->for($reflection),
            new Properties(...$properties),
            new Methods(...$methods),
        );
    }
}
