<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

// Only one use statement with two grouped import statements, no trailing comma.
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\ {
    Foo as FooAlias,
    Bar as BarAlias // no trailing comma
};

// Only one use statement with two grouped import statements, trailing comma.
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\{
    Foo as AnotherFooAlias,
    Bar as AnotherBarAlias, // trailing comma
};

function function_with_grouped_import_statements(
    FooAlias $foo,
    BarAlias $bar,
    AnotherFooAlias $anotherFoo,
    AnotherBarAlias $anotherBar
): void {
}
