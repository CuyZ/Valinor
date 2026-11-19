<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\ReadonlyPropertyWithDefaultValue;

final class ClassWithReadonlyPropertyWithDefaultValue
{
    public readonly string $readonlyWithDefault = 'default value';

    public string $foo;

    public string $bar;
}
