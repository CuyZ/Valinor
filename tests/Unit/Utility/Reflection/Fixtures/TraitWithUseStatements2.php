<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;

trait TraitWithUseStatements2
{
    public Foo $classInNamespaceWithoutAlias;
    public BarAlias $classInNamespaceWithAlias;
}
