<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Builder\Node;
use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\MissingNodeValue;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\ValueDumper;

use function array_fill_keys;
use function array_map;
use function assert;
use function implode;
use function is_int;

/** @internal */
final class Shell
{
    // PHP8.4 private(set)
    // Note: we use public properties and clone, because instantiating a new
    // instance everytime we need to change a value is heavy. Benchmark showed
    // us that this method is more performant.
    public function __construct(
        public string $name,
        public string $path,
        public Type $type,
        private bool $hasValue,
        private mixed $value,
        public Attributes $attributes,
        public bool $allowScalarValueCasting,
        public bool $allowNonSequentialList,
        public bool $allowUndefinedValues,
        public bool $allowSuperfluousKeys,
        public bool $allowPermissiveTypes,
        /** @var array<string, null> */
        public array $allowedSuperfluousKeys,
        public bool $shouldApplyConverters,
        private NodeBuilder $nodeBuilder,
        private TypeDumper $typeDumper,
        /** @var non-negative-int */
        private int $childrenCount,
    ) {
        $this->castFloatValue();
    }

    public function build(): Node
    {
        if ($this->type instanceof UnresolvableType) {
            throw new UnresolvableShellType($this->type);
        }

        if (! $this->hasValue) {
            if (! $this->allowUndefinedValues) {
                return $this->error(MissingNodeValue::from($this->type));
            }

            return $this->nodeBuilder->build($this->withValue(null));
        }

        return $this->nodeBuilder->build($this);
    }

    public function child(string $name, Type $type): self
    {
        $this->childrenCount++;

        $self = clone $this;
        $self->name = $name;
        $self->type = $type;
        $self->path = $this->name === '' ? $name : "$this->path.$name";
        $self->hasValue = false;
        $self->value = null;
        $self->attributes = Attributes::empty();
        $self->childrenCount = 0;

        return $self;
    }

    public function node(mixed $value): Node
    {
        return Node::new($value, $this->childrenCount);
    }

    public function error(Message $error): Node
    {
        return Node::error($this, $error);
    }

    /**
     * @param array<Node> $nodes
     */
    public function errors(array $nodes): Node
    {
        return Node::branchWithErrors($nodes);
    }

    public function withType(Type $newType): self
    {
        $self = clone $this;
        $self->type = $newType;

        $self->castFloatValue();

        return $self;
    }

    public function withValue(mixed $newValue): self
    {
        // @infection-ignore-all / We don't want to test the clone behavior
        $self = clone $this;
        $self->value = $newValue;
        $self->hasValue = true;

        $self->castFloatValue();

        return $self;
    }

    // PHP8.4 replace with hook
    public function value(): mixed
    {
        assert($this->hasValue);

        return $this->value;
    }

    public function withAttributes(Attributes $attributes): self
    {
        // @infection-ignore-all / We don't want to test the clone behavior
        $self = clone $this;
        $self->attributes = $this->attributes->merge($attributes);

        return $self;
    }

    /**
     * @param list<string> $allowedSuperfluousKeys
     */
    public function withAllowedSuperfluousKeys(array $allowedSuperfluousKeys): self
    {
        // @infection-ignore-all / We don't want to test the clone behavior
        $self = clone $this;
        $self->allowedSuperfluousKeys = array_fill_keys($allowedSuperfluousKeys, null);

        return $self;
    }

    public function allowSuperfluousKeys(): self
    {
        $self = clone $this;
        $self->allowSuperfluousKeys = true;

        return $self;
    }

    public function shouldApplyConverters(): self
    {
        // @infection-ignore-all / We don't want to test the clone behavior
        $self = clone $this;
        $self->shouldApplyConverters = true;

        return $self;
    }

    public function shouldNotApplyConverters(): self
    {
        // @infection-ignore-all / We don't want to test the clone behavior
        $self = clone $this;
        $self->shouldApplyConverters = false;

        return $self;
    }

    public function expectedSignature(): string
    {
        if ($this->type instanceof UnionType) {
            return implode(', ', array_map(
                $this->typeDumper->dump(...),
                $this->type->types(),
            ));
        }

        return $this->typeDumper->dump($this->type);
    }

    public function dumpValue(): string
    {
        return $this->hasValue ? ValueDumper::dump($this->value) : '*missing*';
    }

    private function castFloatValue(): void
    {
        // When the value is an integer and the type is a float, the value is
        // cast to float, to follow the rule of PHP regarding acceptance of an
        // integer value in a float type. Note that PHPStan/Psalm analysis
        // applies the same rule.
        if ($this->type instanceof FloatType && is_int($this->value)) {
            $this->value = (float)$this->value;
        }
    }
}
