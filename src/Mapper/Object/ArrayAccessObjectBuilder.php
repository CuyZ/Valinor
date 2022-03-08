<?php

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;

class ArrayAccessObjectBuilder implements ObjectBuilder
{
    private ClassDefinition $class;

    private static Type $argumentType;

    public function __construct(ClassDefinition $class)
    {
        $this->class = $class;
    }

    public function describeArguments(): iterable
    {
        self::$argumentType ??= ArrayType::native();

        yield Argument::required('value', self::$argumentType);
    }

    public function build(array $arguments): object
    {
        $className = $this->class->name();

        $result = new $className;
        foreach ($arguments['value'] as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }
}
