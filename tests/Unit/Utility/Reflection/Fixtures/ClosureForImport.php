<?php

declare(strict_types=1);

namespace {
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
    /**
     * @return class-string<BarAlias::class|Foo::class>
     **/
    return fn () => BarAlias::class;
}

namespace CuyZ\Valinor\Tests\Demo {
    class Demo2 {}
}
