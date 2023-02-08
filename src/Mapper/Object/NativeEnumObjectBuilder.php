<?php

namespace CuyZ\Valinor\Mapper\Object;

use BackedEnum;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\UnionType;

/** @internal */
class NativeEnumObjectBuilder implements ObjectBuilder
{
    private Arguments $arguments;

    private EnumType $enum;

    public function __construct(EnumType $type)
    {
        $types = [];

        foreach ($type->cases() as $case) {
            $value = $case instanceof BackedEnum ? $case->value : $case->name;

            $types[] = ValueTypeFactory::from($value);
        }

        $argumentType = count($types) === 1
            ? $types[0]
            : new UnionType(...$types);

        $this->enum = $type;
        $this->arguments = new Arguments(
            new Argument('value', $argumentType)
        );
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments;
    }

    public function build(array $arguments): object
    {
        return $this->enum->cases()[$arguments['value']];
    }

    public function signature(): string
    {
        return $this->enum->readableSignature();
    }
}
