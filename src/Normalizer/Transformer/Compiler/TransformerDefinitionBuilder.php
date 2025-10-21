<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ClassFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\DateTimeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\DateTimeZoneFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\EnumFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\InterfaceFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\MixedFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\NullFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ScalarFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ShapedArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\StdClassFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TraversableFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnionFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnitEnumFormatter;
use CuyZ\Valinor\Normalizer\Transformer\TransformerContainer;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTimeInterface;
use DateTimeZone;
use stdClass;
use Traversable;
use UnitEnum;

use function array_reverse;
use function is_a;

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
        $definition = new TransformerDefinition(
            type: $type,
            transformerTypes: $this->transformerTypes($type),
            typeFormatter: $this->typeFormatter($type),
        );

        if ($type instanceof ClassType) {
            // A class may have transformer attributes, in which case they are
            // added to the list of attributes for this definition.
            $definition = $definition->withTransformerAttributes(
                $this->classDefinitionRepository
                    ->for($type)
                    ->attributes
                    ->filter(TransformerContainer::filterTransformerAttributes(...))
                    ->toArray(),
            );
        }

        if ($type instanceof MixedType) {
            $definition = $definition->markAsSure();
        }

        return $definition;
    }

    /**
     * @return array<int, Type>
     */
    private function transformerTypes(Type $type): array
    {
        // If the type is a union type, we don't need to add the transformer
        // because it will be added for each subtype anyway. This condition also
        // prevents the transformers from being applied twice in these cases.
        if ($type instanceof UnionType) {
            return [];
        }

        $types = [];

        foreach ($this->transformerContainer->transformers() as $key => $transformer) {
            $function = $this->functionDefinitionRepository->for($transformer);
            $transformerType = $function->parameters->at(0)->type;

            if (! $type->matches($transformerType)) {
                continue;
            }

            $types[$key] = $transformerType;
        }

        return array_reverse($types, preserve_keys: true);
    }

    private function typeFormatter(Type $type): TypeFormatter
    {
        return match (true) {
            $type instanceof EnumType => new EnumFormatter($type),
            $type instanceof InterfaceType => new InterfaceFormatter($type),
            $type instanceof NativeClassType => match (true) {
                $type->className() === UnitEnum::class => new UnitEnumFormatter(),
                $type->className() === stdClass::class => new StdClassFormatter(),
                is_a($type->className(), DateTimeInterface::class, true) => new DateTimeFormatter(),
                is_a($type->className(), DateTimeZone::class, true) => new DateTimeZoneFormatter(),
                is_a($type->className(), Traversable::class, true) => new TraversableFormatter($type->generics()[0] ?? MixedType::get()),
                default => new ClassFormatter($this->classDefinitionRepository->for($type)),
            },
            $type instanceof NullType => new NullFormatter(),
            $type instanceof ScalarType => new ScalarFormatter(),
            $type instanceof ShapedArrayType => new ShapedArrayFormatter($type),
            $type instanceof UnionType => new UnionFormatter($type),
            $type instanceof CompositeTraversableType => new TraversableFormatter($type->subType()),
            $type instanceof GenericType => $this->typeFormatter($type->innerType),
            default => new MixedFormatter(),
        };
    }
}
