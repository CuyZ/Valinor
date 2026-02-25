<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\RegisteredTransformersFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnsureTypeFormatter;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_reverse;

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
     * ```
     * final class SomeClass
     * {
     *     public string $value;
     * }
     * ```
     *
     * However, in the following case, we cannot guarantee the type of the
     * value, as it may contain any value.
     *
     * ```
     *  final class SomeClass
     *  {
     *      // @var string
     *      public $value;
     *  }
     *  ```
     */
    private bool $isSure = false;

    /** @var list<AttributeDefinition> */
    private array $transformerAttributes = [];

    public function __construct(
        public readonly Type $type,
        /** @var array<int, Type> */
        private array $transformerTypes,
        private TypeFormatter $typeFormatter,
    ) {}

    public function typeFormatter(): TypeFormatter
    {
        $typeFormatter = $this->typeFormatter;

        if ($this->transformerTypes !== [] || $this->transformerAttributes !== []) {
            $typeFormatter = new RegisteredTransformersFormatter(
                $this->type,
                $typeFormatter,
                $this->transformerTypes,
                $this->transformerAttributes,
            );
        }

        if (! $this->isSure && ! $this->type instanceof UnresolvableType) {
            return new UnsureTypeFormatter($typeFormatter, $this->type);
        }

        return $typeFormatter;
    }

    /**
     * @param list<AttributeDefinition> $transformerAttributes
     */
    public function withTransformerAttributes(array $transformerAttributes): self
    {
        $self = clone $this;
        $self->transformerAttributes = [...$self->transformerAttributes, ...array_reverse($transformerAttributes)];

        return $self;
    }

    public function hasTransformers(): bool
    {
        return $this->transformerTypes !== [] || $this->transformerAttributes !== [];
    }

    public function markAsSure(): self
    {
        $self = clone $this;
        $self->isSure = true;

        return $self;
    }
}
