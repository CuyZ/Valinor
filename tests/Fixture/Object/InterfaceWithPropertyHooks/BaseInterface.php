<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

interface BaseInterface
{
    public string|null $deleted { get; }
}
