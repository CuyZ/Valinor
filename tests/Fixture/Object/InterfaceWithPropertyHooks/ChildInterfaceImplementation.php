<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

final class ChildInterfaceImplementation implements ChildInterface
{
    public string $name;

    public string|null $deleted = null;
}
