<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TransformerDefinition
{
    public readonly Type $nativeType;

    public function __construct(
        public readonly Type $type,
        /** @var array<int, Type> */
        public readonly array $transformerTypes,
        /** @var list<AttributeDefinition> */
        public readonly array $transformerAttributes,
        /** @var list<AttributeDefinition> */
        public readonly array $keyTransformerAttributes,
        public readonly TypeTransformer $typeTransformer,
    ) {}

    public function withNativeType(Type $nativeType): self
    {
        $self = clone $this;
        $self->nativeType = $nativeType;

        return $self;
    }

    public function hasTransformation(): bool
    {
        return $this->transformerTypes !== [] || $this->transformerAttributes !== [];
    }

    public function hasKeyTransformation(): bool
    {
        return $this->keyTransformerAttributes !== [];
    }
}
