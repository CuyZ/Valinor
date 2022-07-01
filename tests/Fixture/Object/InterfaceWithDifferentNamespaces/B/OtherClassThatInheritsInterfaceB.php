<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\B;

use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceB;

final class OtherClassThatInheritsInterfaceB implements InterfaceB
{
    public string $value;
}
