<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;

use function array_map;
use function array_values;
use function assert;
use function count;

/** @internal */
final class ClassParentTypeResolver
{
    private ClassGenericResolver $genericResolver;

    private TemplateResolver $templateResolver;

    public function __construct(private TypeParserFactory $typeParserFactory)
    {
        $this->genericResolver = new ClassGenericResolver($this->typeParserFactory);
        $this->templateResolver = new TemplateResolver();
    }

    public function resolveParentTypeFor(ObjectType $child): NativeClassType
    {
        assert($child instanceof NativeClassType);

        $reflection = Reflection::class($child->className());

        /** @var ReflectionClass<covariant object> $parentReflection */
        $parentReflection = $reflection->getParentClass();

        $extendedClass = $this->extractParentTypeFromDocBlock($reflection);

        if (count($extendedClass) > 1) {
            return $this->fillParentGenericsWithUnresolvableTypes($parentReflection, UnresolvableType::forSeveralExtendTagsFound($reflection->name));
        } elseif (count($extendedClass) === 0) {
            $extendedClass = $parentReflection->name;
        } else {
            $extendedClass = $extendedClass[0];
        }

        $generics = $this->genericResolver->resolveGenerics($child);

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($child->className());
        $typeParser = new VacantTypeAssignerParser($typeParser, $generics);

        $parentType = $typeParser->parse($extendedClass);

        if ($parentType instanceof UnresolvableType) {
            return $this->fillParentGenericsWithUnresolvableTypes($parentReflection, $parentType->forExtendTagTypeError($reflection->name));
        }

        if (! $parentType instanceof NativeClassType || $parentType->className() !== $parentReflection->name) {
            return $this->fillParentGenericsWithUnresolvableTypes($parentReflection, UnresolvableType::forInvalidExtendTagType($reflection->name, $parentReflection->name, $parentType));
        }

        return $parentType;
    }

    /**
     * @param ReflectionClass<covariant object> $reflection
     * @return list<non-empty-string>
     */
    private function extractParentTypeFromDocBlock(ReflectionClass $reflection): array
    {
        $annotations = Annotations::forParents($reflection->name);

        return array_map(
            fn (TokenizedAnnotation $annotation) => $annotation->raw(),
            $annotations,
        );
    }

    /**
     * @param ReflectionClass<covariant object> $class
     */
    private function fillParentGenericsWithUnresolvableTypes(ReflectionClass $class, UnresolvableType $unresolvableType): NativeClassType
    {
        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($class->name);

        $templates = $this->templateResolver->templatesFromDocBlock($class, $class->name, $typeParser);
        $generics = array_values(array_map(
            static fn (GenericType $type) => new UnresolvableType($type->symbol, $unresolvableType->message()),
            $templates,
        ));

        return new NativeClassType($class->name, $generics);
    }
}
