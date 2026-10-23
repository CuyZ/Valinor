<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

interface GrandChildInterface extends ChildInterface
{
    public bool $active { get; }
}
