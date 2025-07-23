<?php

declare(strict_types=1);

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo as FooAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;

function function_in_root_namespace(
    FooAlias $foo,
    BarAlias $bar
): void {}
