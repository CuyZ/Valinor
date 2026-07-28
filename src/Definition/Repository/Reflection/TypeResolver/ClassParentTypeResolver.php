<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

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

    public function resolveParentTypeFor(NativeClassType|InterfaceType $child, ReflectionProperty|ReflectionMethod $member): ObjectType
    {
        return match ($child::class) {
            NativeClassType::class => $this->resolveParentTypeForClass($child),
            InterfaceType::class => $this->resolveParentTypeForInterface($child, $member),
        };
    }

    private function resolveParentTypeForClass(NativeClassType $child): NativeClassType
    {
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

        $typeParser = $this->buildTypeParserFor($child);

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
     * The member may be declared in any of the interfaces that the given
     * reflection extends or implements, possibly deeply nested, so the direct
     * parent interface that provides it needs to be found first.
     */
    private function resolveParentTypeForInterface(InterfaceType $child, ReflectionProperty|ReflectionMethod $member): InterfaceType
    {
        $reflection = Reflection::class($child->className());
        $parent = $this->findDeclaringInterface($reflection, $member);

        $extendedInterfaces = $this->extractParentTypeFromDocBlock($reflection);

        $typeParser = $this->buildTypeParserFor($child);

        $parentType = null;
        $unresolvableType = null;

        foreach ($extendedInterfaces as $extendedInterface) {
            $parsed = $typeParser->parse($extendedInterface);

            if ($parsed instanceof UnresolvableType) {
                $unresolvableType = $parsed;
            } elseif ($parsed instanceof InterfaceType && $parsed->className() === $parent->name) {
                if ($parentType !== null) {
                    return $this->fillParentInterfaceGenericsWithUnresolvableTypes($parent, UnresolvableType::forSeveralExtendTagsFound($reflection->name));
                }

                $parentType = $parsed;
            }
        }

        if ($parentType !== null) {
            return $parentType;
        }

        if ($unresolvableType !== null) {
            return $this->fillParentInterfaceGenericsWithUnresolvableTypes($parent, $unresolvableType->forExtendTagTypeError($reflection->name));
        }

        $parentType = $typeParser->parse($parent->name);

        if ($parentType instanceof UnresolvableType) {
            return $this->fillParentInterfaceGenericsWithUnresolvableTypes($parent, $parentType->forExtendTagTypeError($reflection->name));
        }

        assert($parentType instanceof InterfaceType);

        return $parentType;
    }

    /**
     * Among the interfaces directly extended by the given reflection, finds the
     * most-derived one that declares the member. Interfaces extended by another
     * interface in the same set are skipped so only the direct parent remains.
     * Falls back to the member's own declaring class when none matches.
     *
     * @param ReflectionClass<covariant object> $reflection
     * @return ReflectionClass<covariant object>
     */
    private function findDeclaringInterface(ReflectionClass $reflection, ReflectionProperty|ReflectionMethod $member): ReflectionClass
    {
        $interfaces = $reflection->getInterfaces();
        $parent = $member->getDeclaringClass();

        foreach ($interfaces as $interface) {
            foreach ($interfaces as $other) {
                if ($other->name !== $interface->name && $other->implementsInterface($interface->name)) {
                    continue 2;
                }
            }

            $hasMember = $member instanceof ReflectionProperty
                ? $interface->hasProperty($member->name)
                : $interface->hasMethod($member->name);

            if ($hasMember) {
                $parent = $interface;
            }
        }

        return $parent;
    }

    private function buildTypeParserFor(NativeClassType|InterfaceType $child): TypeParser
    {
        $generics = $this->genericResolver->resolveGenerics($child);

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($child->className());

        return new VacantTypeAssignerParser($typeParser, $generics);
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
        return new NativeClassType($class->name, $this->unresolvableGenericsFor($class, $unresolvableType));
    }

    /**
     * @param ReflectionClass<covariant object> $interface
     */
    private function fillParentInterfaceGenericsWithUnresolvableTypes(ReflectionClass $interface, UnresolvableType $unresolvableType): InterfaceType
    {
        return new InterfaceType($interface->name, $this->unresolvableGenericsFor($interface, $unresolvableType));
    }

    /**
     * @param ReflectionClass<covariant object> $class
     * @return list<UnresolvableType>
     */
    private function unresolvableGenericsFor(ReflectionClass $class, UnresolvableType $unresolvableType): array
    {
        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($class->name);

        $templates = $this->templateResolver->templatesFromDocBlock($class, $class->name, $typeParser);

        return array_values(array_map(
            static fn (GenericType $type) => new UnresolvableType($type->symbol, $unresolvableType->message()),
            $templates,
        ));
    }
}
