<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition;

use ArrayObject;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\ArrayObjectTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\ClassTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\DateTimeTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\DateTimeZoneTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\EnumTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\MixedTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\NullTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\ScalarTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\StdClassTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\DelegateTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TraversableTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;
use CuyZ\Valinor\Normalizer\Transformer\TransformerContainer;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use stdClass;

/** @internal */
final class TransformerDefinitionBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        private TransformerContainer $transformerContainer,
    ) {}

    public function for(Type $type): TransformerDefinition
    {
        return $this->buildDefinitionFor($type, [], []);
    }

    /**
     * @param list<AttributeDefinition> $transformerAttributes
     * @param list<AttributeDefinition> $keyTransformerAttributes
     */
    private function buildDefinitionFor(Type $type, array $transformerAttributes, array $keyTransformerAttributes): TransformerDefinition
    {
        $transformerTypes = [];
        $typeTransformer = $this->typeTransformer($type);

        if ($type instanceof NativeClassType) {
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

        return new TransformerDefinition(
            $type,
            $transformerTypes,
            $transformerAttributes,
            $keyTransformerAttributes,
            $typeTransformer,
        );
    }

    public function typeTransformer(Type $type): TypeTransformer
    {
        return match (true) {
            $type instanceof CompositeTraversableType => new TraversableTransformerNode($this->for($type->subType())),
            $type instanceof EnumType => new EnumTransformerNode($type),
            $type instanceof MixedType => new MixedTransformerNode([
                $this->for(NativeBooleanType::get()),
                $this->for(NativeFloatType::get()),
                $this->for(NativeIntegerType::get()),
                $this->for(NativeStringType::get()),
                // @todo handle TraversableType
            ]),
            $type instanceof NativeClassType => $this->classDefaultTransformer($type),
            $type instanceof NullType => new NullTransformerNode(),
            $type instanceof ScalarType => new ScalarTransformerNode(),
            //            $type instanceof ShapedArrayType => , // @todo
            default => new DelegateTransformerNode(),
        };
    }

    private function classDefaultTransformer(NativeClassType $type): TypeTransformer
    {
        if ($type->className() === stdClass::class) {
            return new StdClassTransformerNode();
        } elseif (is_a($type->className(), ArrayObject::class, true)) {
            return new ArrayObjectTransformerNode();
        } elseif (is_a($type->className(), DateTimeInterface::class, true)) {
            return new DateTimeTransformerNode();
        } elseif (is_a($type->className(), DateTimeZone::class, true)) {
            return new DateTimeZoneTransformerNode();
        } elseif (is_a($type->className(), Generator::class, true)) {
            // @todo handle Generator generic types
            return new TraversableTransformerNode($this->for(MixedType::get()));
        }

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

            $definitions[$property->name] = $this->buildDefinitionFor($property->type, $transformerAttributes, $keyTransformerAttributes);
        }

        return new ClassTransformerNode($type, $definitions);
    }
}
