<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

interface ChildInterface extends BaseInterface
{
    public string $name { get; }
}
