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
    /**
     * This flag is used to indicate that we are sure what the type of this
     * definition is. This is important to ensure that the normalizer will act
     * correctly, even if the value of a property does not respect the expected
     * type.
     *
     * For instance, in the following case we know that the value is a string:
     *
     * ```php
     * final class SomeClass
     * {
     *     public string $value;
     * }
     * ```
     *
     * However, in the following case, we cannot guarantee the type of the
     * value, as it may contain any value.
     *
     * ```php
     *  final class SomeClass
     *  {
     *      /** @var string *\
     *      public $value;
     *  }
     *  ```
     */
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
