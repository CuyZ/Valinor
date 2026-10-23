<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

final class ChildInterfaceWithSeveralParentsImplementation implements ChildInterfaceWithSeveralParents
{
    public string|null $deleted = null;

    public int $count;
}
