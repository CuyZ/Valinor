<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces;

use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\B\ClassThatInheritsInterfaceB;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\B\OtherClassThatInheritsInterfaceB;

final class InterfaceBInferer
{
    /**
     * @return class-string<ClassThatInheritsInterfaceB|OtherClassThatInheritsInterfaceB>
     */
    public static function infer(bool $classic): string
    {
        return $classic
            ? ClassThatInheritsInterfaceB::class
            : OtherClassThatInheritsInterfaceB::class;
    }
}
