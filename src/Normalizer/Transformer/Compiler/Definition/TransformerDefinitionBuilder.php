<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition;

use ArrayObject;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeZoneDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\EnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\InterfaceDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\MixedDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\NullDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ScalarDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ShapedArrayDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\StdClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\TraversableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnitEnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\FormatterCompiler;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\RegisteredTransformersFormatter;
use CuyZ\Valinor\Normalizer\Transformer\TransformerContainer;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;
use stdClass;
use Traversable;
use UnitEnum;

/** @internal */
final class TransformerDefinitionBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        private TransformerContainer $transformerContainer,
    ) {}

    /**
     * @param list<AttributeDefinition> $transformerAttributes
     * @param list<AttributeDefinition> $keyTransformerAttributes
     */
    public function for(
        Type $type,
        FormatterCompiler $compiler,
        ArrayObject $classesReferences = new ArrayObject(),
        array $transformerAttributes = [],
        array $keyTransformerAttributes = [],
    ): TransformerDefinition {
        $transformerTypes = [];

        if ($type instanceof ClassType) {
            // A class may have transformer attributes, in which case they are
            // added to the list of attributes for this definition.
            $transformerAttributes = [
                ...$transformerAttributes,
                ...$this->classDefinitionRepository
                    ->for($type)
                    ->attributes
                    ->filter($this->transformerContainer->filterTransformerAttributes(...))
                    ->toArray(),
            ];
        }

        foreach ($this->transformerContainer->transformers() as $key => $transformer) {
            $function = $this->functionDefinitionRepository->for($transformer);
            $transformerType = $function->parameters->at(0)->type;

            if (! $type->matches($transformerType)) {
                continue;
            }

            $transformerTypes[$key] = $transformerType;
        }

        $transformerTypes = array_reverse($transformerTypes, preserve_keys: true);
        $transformerAttributes = array_reverse($transformerAttributes);

        $definitionNode = $this->definitionNode($type, $compiler, $classesReferences);

        $typeTransformer = $compiler->typeTransformer($definitionNode);

        if ($transformerTypes !== [] || $transformerAttributes !== []) {
            $typeTransformer = new RegisteredTransformersFormatter(
                $type,
                $typeTransformer,
                $transformerTypes,
                $transformerAttributes,
            );
        }

        return new TransformerDefinition(
            $type,
            $transformerTypes,
            $transformerAttributes,
            $keyTransformerAttributes,
            $typeTransformer,
        );
    }

    public function definitionNode(Type $type, FormatterCompiler $formatter, ArrayObject $classesReferences): DefinitionNode
    {
        return match (true) {
            $type instanceof CompositeTraversableType => new TraversableDefinitionNode(
                $this->for($type->subType(), $formatter, $classesReferences),
            ),
            $type instanceof EnumType => new EnumDefinitionNode($type),
            $type instanceof InterfaceType => new InterfaceDefinitionNode($type),
            $type instanceof MixedType => new MixedDefinitionNode([
                $this->for(NativeBooleanType::get(), $formatter, $classesReferences),
                $this->for(NativeFloatType::get(), $formatter, $classesReferences),
                $this->for(NativeIntegerType::get(), $formatter, $classesReferences),
                $this->for(NativeStringType::get(), $formatter, $classesReferences),
                $this->for(NullType::get(), $formatter, $classesReferences),
                $this->for(new NativeClassType(UnitEnum::class), $formatter, $classesReferences),
                $this->for(new NativeClassType(DateTime::class), $formatter, $classesReferences),
                $this->for(new NativeClassType(DateTimeZone::class), $formatter, $classesReferences),
                // @todo handle TraversableType
            ]),
            $type instanceof NativeClassType => $this->classDefinitionNode($type, $formatter, $classesReferences),
            $type instanceof NullType => new NullDefinitionNode(),
            $type instanceof ScalarType => new ScalarDefinitionNode(),
            $type instanceof ShapedArrayType => $this->shapedArrayDefinitionNode($type, $formatter, $classesReferences),
            default => throw new RuntimeException('@todo : ' . $type::class), // @todo
        };
    }

    private function classDefinitionNode(NativeClassType $type, FormatterCompiler $formatter, ArrayObject $classesReferences): DefinitionNode
    {
        if ($type->className() === UnitEnum::class) {
            return new UnitEnumDefinitionNode();
        } elseif ($type->className() === stdClass::class) {
            return new StdClassDefinitionNode();
        } elseif (is_a($type->className(), DateTimeInterface::class, true)) {
            return new DateTimeDefinitionNode();
        } elseif (is_a($type->className(), DateTimeZone::class, true)) {
            return new DateTimeZoneDefinitionNode();
        } elseif (is_a($type->className(), Traversable::class, true)) {
            // @todo handle Generator generic types
            return new TraversableDefinitionNode($this->for(MixedType::get(), $formatter, $classesReferences));
        }

        if (isset($classesReferences[$type->toString()])) {
            return $classesReferences[$type->toString()];
        }

        $classesReferences[$type->toString()] = new ClassDefinitionNode($type, []);

        $definitions = [];

        $class = $this->classDefinitionRepository->for($type);

        foreach ($class->properties as $property) {
            $keyTransformerAttributes = $property->attributes
                ->filter($this->transformerContainer->filterKeyTransformerAttributes(...))
                ->toArray();

            $transformerAttributes = $property->attributes
                ->filter($this->transformerContainer->filterTransformerAttributes(...))
                ->filter(
                    fn (AttributeDefinition $attribute): bool => $property->type->matches(
                        $attribute->class->methods->get('normalize')->parameters->at(0)->type,
                    ),
                )->toArray();

            $definition = $this->for($property->type, $formatter, $classesReferences, $transformerAttributes, $keyTransformerAttributes);
            $definition = $definition->withNativeType($property->nativeType);

            $definitions[$property->name] = $definition;
        }

        $classesReferences[$type->toString()]->propertiesDefinitions = $definitions;

        return $classesReferences[$type->toString()];
    }

    private function shapedArrayDefinitionNode(ShapedArrayType $type, FormatterCompiler $formatter, ArrayObject $classesReferences): DefinitionNode
    {
        $definitions = [];

        foreach ($type->elements() as $element) {
            $definitions[$element->key()->toString()] = $this->for($element->type(), $formatter, $classesReferences);
        }

        $defaultDefinition = $this->for(MixedType::get(), $formatter, $classesReferences);

        return new ShapedArrayDefinitionNode($type, $defaultDefinition, $definitions);
    }
}
