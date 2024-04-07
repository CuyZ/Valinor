<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Exception\ExtendTagTypeError;
use CuyZ\Valinor\Definition\Exception\InvalidExtendTagClassName;
use CuyZ\Valinor\Definition\Exception\InvalidExtendTagType;
use CuyZ\Valinor\Definition\Exception\SeveralExtendTagsFound;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;
use CuyZ\Valinor\Type\Parser\Lexer\Annotations;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;

use function array_map;

/** @internal */
final class ClassParentTypeResolver
{
    public function __construct(private TypeParserFactory $typeParserFactory) {}

    public function resolveParentTypeFor(ObjectType $type): NativeClassType
    {
        $reflection = Reflection::class($type->className());

        /** @var ReflectionClass<object> $parentReflection */
        $parentReflection = $reflection->getParentClass();

        $extendedClass = $this->extractParentTypeFromDocBlock($reflection);

        if (count($extendedClass) > 1) {
            throw new SeveralExtendTagsFound($reflection);
        } elseif (count($extendedClass) === 0) {
            $extendedClass = $parentReflection->name;
        } else {
            $extendedClass = $extendedClass[0];
        }

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($type);

        try {
            $parentType = $typeParser->parse($extendedClass);
        } catch (InvalidType $exception) {
            throw new ExtendTagTypeError($reflection, $exception);
        }

        if (! $parentType instanceof NativeClassType) {
            throw new InvalidExtendTagType($reflection, $parentType);
        }

        if ($parentType->className() !== $parentReflection->name) {
            throw new InvalidExtendTagClassName($reflection, $parentType);
        }

        return $parentType;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return list<non-empty-string>
     */
    private function extractParentTypeFromDocBlock(ReflectionClass $reflection): array
    {
        $docBlock = $reflection->getDocComment();

        if ($docBlock === false) {
            return [];
        }

        $annotations = (new Annotations($docBlock))->filteredByPriority(
            '@phpstan-extends',
            '@psalm-extends',
            '@extends',
        );

        return array_map(
            fn (TokenizedAnnotation $annotation) => $annotation->raw(),
            $annotations,
        );
    }
}
