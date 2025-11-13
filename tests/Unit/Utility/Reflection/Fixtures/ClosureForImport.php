<?php

declare(strict_types=1);

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;

/**
 * @return class-string<BarAlias::class|Foo::class>
 */
$closure = fn () => BarAlias::class;

return $closure;
