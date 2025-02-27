<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnsureTypeFormatter;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TransformerDefinition
{
    private bool $isSure = false;

    public function __construct(
        public readonly Type $type,
        /** @var array<int, Type> */
        public readonly array $transformerTypes,
        /** @var list<AttributeDefinition> */
        public readonly array $transformerAttributes,
        /** @var list<AttributeDefinition> */
        public readonly array $keyTransformerAttributes,
        private readonly TypeFormatter $typeFormatter,
    ) {}

    public function typeFormatter(): TypeFormatter
    {
        if (! $this->isSure) {
            return new UnsureTypeFormatter($this->typeFormatter, $this->type);
        }

        return $this->typeFormatter;
    }

    public function markAsSure(): self
    {
        $self = clone $this;
        $self->isSure = true;

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
