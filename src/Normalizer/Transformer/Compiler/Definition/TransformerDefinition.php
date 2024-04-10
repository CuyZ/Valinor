<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Node\RegisteredTransformersNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeConditionTransformerNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;

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

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        if ($this->type instanceof MixedType || ! $this->hasTransformation()) {
            return $this->typeTransformer()->valueTransformationNode($valueNode);
        }

        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->typeTransformer()->manipulateTransformerClass($class);

        if ($this->type instanceof MixedType) {
            return $class;
        }

        $methodName = $this->methodName();

        if (! $this->hasTransformation() || $class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                )
                ->withReturnType('mixed')
                ->withBody(new RegisteredTransformersNode($this)),
        );
    }

    public function hasTransformation(): bool
    {
        return $this->transformerTypes !== [] || $this->transformerAttributes !== [];
    }

    public function hasKeyTransformation(): bool
    {
        return $this->keyTransformerAttributes !== [];
    }

    private function typeTransformer(): TypeTransformer
    {
        if (! isset($this->nativeType) || $this->nativeType instanceof MixedType) {
            return new TypeConditionTransformerNode($this->type, $this->typeTransformer);
        }

        return $this->typeTransformer;
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->type->toString()));

        return "transform_{$slug}_" . sha1(serialize([$this->type->toString(), $this->transformerAttributes]));
    }
}
