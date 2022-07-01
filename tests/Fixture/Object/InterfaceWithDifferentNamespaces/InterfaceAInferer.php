<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces;

use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\A\ClassThatInheritsInterfaceA;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\A\OtherClassThatInheritsInterfaceA;

final class InterfaceAInferer
{
    /**
     * @return class-string<ClassThatInheritsInterfaceA|OtherClassThatInheritsInterfaceA>
     */
    public static function infer(bool $classic): string
    {
        return $classic
            ? ClassThatInheritsInterfaceA::class
            : OtherClassThatInheritsInterfaceA::class;
    }
}
