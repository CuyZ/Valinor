<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Exception\PermissiveTypeNotAllowed;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

/** @internal */
final class StrictTypesObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(private ObjectBuilderFactory $delegate) {}

    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        foreach ($builders as $builder) {
            $arguments = $builder->describeArguments();

            foreach ($arguments as $argument) {
                $this->checkPresenceOfPermissiveType($argument, $argument->type());
            }
        }

        return $builders;
    }

    private function checkPresenceOfPermissiveType(Argument $argument, Type $type): void
    {
        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                self::checkPresenceOfPermissiveType($argument, $subType);
            }
        }

        if ($type instanceof MixedType || $type instanceof UndefinedObjectType) {
            throw new PermissiveTypeNotAllowed($argument->signature(), $type);
        }
    }
}
