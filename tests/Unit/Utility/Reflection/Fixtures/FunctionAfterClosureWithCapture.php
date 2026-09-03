<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;

function function_with_closure_capture(bool $captured): callable
{
    return static function (object $value) use ($captured): bool {
        $accepted = [Foo::class, BarAlias::class];

        return $captured && $accepted[0] === $value::class;
    };
}

function function_after_closure_with_capture(Foo $foo, BarAlias $bar): void {}
