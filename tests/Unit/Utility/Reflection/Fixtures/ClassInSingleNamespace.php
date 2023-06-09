<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
use DateTimeImmutable;
use stdClass as stdClassAlias;

final class ClassInSingleNamespace
{
    public function __construct(
        public Foo $classInNamespaceWithoutAlias,
        public BarAlias $classInNamespaceWithAlias,
        public DateTimeImmutable $classInRootNamespaceWithoutAlias,
        public stdClassAlias $classInRootNamespaceWithAlias
    ) {}
}
