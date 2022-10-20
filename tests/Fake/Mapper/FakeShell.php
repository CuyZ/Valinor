<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;

final class FakeShell
{
    public static function new(Type $type, mixed $value = null): Shell
    {
        return Shell::root($type, $value);
    }

    public static function any(): Shell
    {
        return self::new(new FakeType(), []);
    }
}
