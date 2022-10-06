<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

// Only one use statement with two import statements.
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo as FooAlias,
    CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo as AnotherFooAlias,
    CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as AnotherBarAlias;

function function_with_several_import_statements_in_same_use_statement(
    FooAlias $foo,
    BarAlias $bar,
    AnotherFooAlias $anotherFoo,
    AnotherBarAlias $anotherBar
): void {
}
