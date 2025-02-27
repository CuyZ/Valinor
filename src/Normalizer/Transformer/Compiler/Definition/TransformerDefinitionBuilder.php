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
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnionDefinitionNode;
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
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use LogicException;
use stdClass;
use Traversable;
use UnitEnum;

use function array_map;

/** @internal */
final class TransformerDefinitionBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        private TransformerContainer $transformerContainer,
        private FormatterCompiler $formatterCompiler,
    ) {}

    /**
     * @param ArrayObject<non-empty-string, ClassDefinitionNode> $classesReferences
     * @param list<AttributeDefinition> $transformerAttributes
     * @param list<AttributeDefinition> $keyTransformerAttributes
     */
    public function for(
        Type $type,
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

        $definitionNode = $this->definitionNode($type, $classesReferences);

        $typeFormatter = $this->formatterCompiler->typeFormatter($definitionNode);

        if ($transformerTypes !== [] || $transformerAttributes !== []) {
            $typeFormatter = new RegisteredTransformersFormatter(
                $type,
                $typeFormatter,
                $transformerTypes,
                $transformerAttributes,
            );
        }

        $definition = new TransformerDefinition(
            $type,
            $transformerTypes,
            $transformerAttributes,
            $keyTransformerAttributes,
            $typeFormatter,
        );

        if ($type instanceof MixedType) {
            $definition = $definition->markAsSure();
        }

        return $definition;
    }

    /**
     * @param ArrayObject<non-empty-string, ClassDefinitionNode> $classesReferences
     */
    public function definitionNode(Type $type, ArrayObject $classesReferences): DefinitionNode
    {
        return match (true) {
            $type instanceof CompositeTraversableType => new TraversableDefinitionNode(
                $type,
                $this->for(MixedType::get(), $classesReferences),
                $this->for($type->subType(), $classesReferences),
            ),
            $type instanceof EnumType => new EnumDefinitionNode($type),
            $type instanceof InterfaceType => new InterfaceDefinitionNode($type),
            $type instanceof MixedType => new MixedDefinitionNode([
                $this->for(NativeBooleanType::get(), $classesReferences)->markAsSure(),
                $this->for(NativeFloatType::get(), $classesReferences)->markAsSure(),
                $this->for(NativeIntegerType::get(), $classesReferences)->markAsSure(),
                $this->for(NativeStringType::get(), $classesReferences)->markAsSure(),
                $this->for(NullType::get(), $classesReferences)->markAsSure(),
                $this->for(new NativeClassType(UnitEnum::class), $classesReferences)->markAsSure(),
                $this->for(new NativeClassType(DateTime::class), $classesReferences)->markAsSure(),
                $this->for(new NativeClassType(DateTimeZone::class), $classesReferences)->markAsSure(),
                // @todo handle TraversableType
            ]),
            $type instanceof NativeClassType => $this->classDefinitionNode($type, $classesReferences),
            $type instanceof NullType => new NullDefinitionNode(),
            $type instanceof ScalarType => new ScalarDefinitionNode(),
            $type instanceof ShapedArrayType => $this->shapedArrayDefinitionNode($type, $classesReferences),
            $type instanceof UnionType => $this->unionDefinitionNode($type, $classesReferences),
            default => throw new LogicException('Unhandled type ' . $type::class),
        };
    }

    /**
     * @param ArrayObject<non-empty-string, ClassDefinitionNode> $classesReferences
     */
    private function classDefinitionNode(NativeClassType $type, ArrayObject $classesReferences): DefinitionNode
    {
        if ($type->className() === UnitEnum::class) {
            return new UnitEnumDefinitionNode();
        } elseif ($type->className() === stdClass::class) {
            return new StdClassDefinitionNode($this->for(MixedType::get(), $classesReferences));
        } elseif (is_a($type->className(), DateTimeInterface::class, true)) {
            return new DateTimeDefinitionNode();
        } elseif (is_a($type->className(), DateTimeZone::class, true)) {
            return new DateTimeZoneDefinitionNode();
        } elseif (is_a($type->className(), Traversable::class, true)) {
            // @todo handle Generator generic types
            return new TraversableDefinitionNode(
                IterableType::native(),
                $this->for(MixedType::get(), $classesReferences),
                $this->for(MixedType::get(), $classesReferences),
            );
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

            $definition = $this->for($property->type, $classesReferences, $transformerAttributes, $keyTransformerAttributes);

            if (! $property->nativeType instanceof MixedType) {
                $definition = $definition->markAsSure();
            }

            $definitions[$property->name] = $definition;
        }

        $classesReferences[$type->toString()]->propertiesDefinitions = $definitions;

        return $classesReferences[$type->toString()];
    }

    /**
     * @param ArrayObject<non-empty-string, ClassDefinitionNode> $classesReferences
     */
    private function shapedArrayDefinitionNode(ShapedArrayType $type, ArrayObject $classesReferences): DefinitionNode
    {
        $definitions = [];

        foreach ($type->elements() as $element) {
            $definitions[$element->key()->value()] = $this->for($element->type(), $classesReferences);
        }

        if ($type->isUnsealed() && $type->hasUnsealedType()) {
            $defaultDefinition = $this->for($type->unsealedType()->subType(), $classesReferences);
        } else {
            $defaultDefinition = $this->for(MixedType::get(), $classesReferences);
        }

        return new ShapedArrayDefinitionNode($type, $defaultDefinition, $definitions);
    }

    /**
     * @param ArrayObject<non-empty-string, ClassDefinitionNode> $classesReferences
     */
    private function unionDefinitionNode(UnionType $type, ArrayObject $classesReferences): DefinitionNode
    {
        $definitions = array_map(
            fn (Type $type) => $this->for($type, $classesReferences)->markAsSure(),
            $type->types(),
        );

        $defaultDefinition = $this->for(MixedType::get(), $classesReferences);

        return new UnionDefinitionNode($type, $defaultDefinition, $definitions);
    }
}
