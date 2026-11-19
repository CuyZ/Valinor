<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\ReadonlyPropertyWithDefaultValue;

final readonly class ReadonlyClassWithPropertyWithDefaultValue
{
    public string $withDefault = 'default value';

    public string $foo;

    public string $bar;
}
