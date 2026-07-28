<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

/**
 * @template T
 */
interface GenericBaseInterface
{
    /** @var T */
    public mixed $genericValue { get; }
}
