<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

final class GrandChildInterfaceImplementation implements GrandChildInterface
{
    public string $name;

    public string|null $deleted = null;

    public bool $active;
}
