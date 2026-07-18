<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\AnonymousClassNamespace;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;

return new class (null, null) {
    public function __construct(
        public BarAlias|null $bar,
        public Foo|null $foo,
    ) {}
};
