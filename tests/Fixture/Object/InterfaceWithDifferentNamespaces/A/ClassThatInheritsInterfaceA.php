<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\A;

use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceA;

final class ClassThatInheritsInterfaceA implements InterfaceA
{
    public string $value;
}
