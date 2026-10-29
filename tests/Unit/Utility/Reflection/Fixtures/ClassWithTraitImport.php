<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use Closure;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;

trait TraitForImport
{
    public function traitMethod(): string
    {
        return self::class;
    }
}

final class ClassWithTraitImport
{
    use TraitForImport {
        traitMethod as foo;
    }

    public function closure(): Closure
    {
        return fn (BarAlias $bar): Foo => new Foo();
    }
}
